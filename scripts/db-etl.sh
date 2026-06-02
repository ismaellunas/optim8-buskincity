#!/usr/bin/env bash
#
# db-etl.sh — PostgreSQL backup / structure-preserving export-reimport / safe migrate.
#
# CONTEXT
#   The working DB is whatever .env points to (currently a REMOTE RDS instance).
#   NEVER run `migrate:fresh` here — it would wipe RDS. Use `safe-migrate` instead,
#   which snapshots first.
#
# THE MODEL (export current data -> change shape -> reimport)
#   * ADDITIVE migration (new column/table): just `safe-migrate` — data is preserved,
#     no reimport needed.
#   * DESTRUCTIVE migration (drop/rename/type change): export -> migrate -> import-data.
#     The data dump uses `--column-inserts` (rows are INSERTs with explicit column
#     names), so it tolerates added/reordered columns automatically. Dropped/renamed
#     columns need a manual edit of data.sql before import-data.
#
# COMMANDS  (see `db-etl.sh help`)
#   export [out-dir]                 snapshot: full.dump + schema.sql + data.sql
#   safe-migrate [artisan args...]   export, then `sail artisan migrate ...` (everyday)
#   import-data <dir> [--truncate]   reload data.sql into the current schema
#   restore-full <dir|full.dump>     exact rollback from full.dump
#   clone <target-env-file>          copy THIS db -> the db described by target env file
#
# Credentials come from .env (override the source file with ENV_FILE=...).
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

ENV_FILE="${ENV_FILE:-.env}"
SAIL="${SAIL:-./vendor/bin/sail}"
PG_SERVICE="${PG_SERVICE:-pgsql}"   # Sail service whose image ships pg client tools
ASSUME_YES="${ASSUME_YES:-0}"

# ---------------------------------------------------------------------------
log()  { printf '\033[1;34m[etl]\033[0m %s\n' "$*" >&2; }
err()  { printf '\033[1;31m[etl] ERROR:\033[0m %s\n' "$*" >&2; }
die()  { err "$*"; exit 1; }

# read KEY from an env file (strips CR; first match wins)
envget() { grep -E "^$1=" "$2" 2>/dev/null | head -1 | sed -E "s/^$1=//; s/\r$//"; }

# load the SOURCE connection (the working DB) into DB_* globals
load_env() {
  [ -f "$ENV_FILE" ] || die "missing $ENV_FILE"
  DB_HOST="$(envget DB_HOST "$ENV_FILE")"
  DB_PORT="$(envget DB_PORT "$ENV_FILE")"
  DB_DATABASE="$(envget DB_DATABASE "$ENV_FILE")"
  DB_USERNAME="$(envget DB_USERNAME "$ENV_FILE")"
  DB_PASSWORD="$(envget DB_PASSWORD "$ENV_FILE")"
  DB_SSLMODE="$(envget DB_SSLMODE "$ENV_FILE")"
  : "${DB_HOST:?DB_HOST missing in $ENV_FILE}"
  : "${DB_DATABASE:?DB_DATABASE missing}" "${DB_USERNAME:?DB_USERNAME missing}"
  DB_PORT="${DB_PORT:-5432}"
  DB_SSLMODE="${DB_SSLMODE:-prefer}"
}

# run a pg client tool against an explicit connection, preferring host binaries,
# else the Sail pgsql container. stdin/stdout pass through (binary-safe).
#   run_pg <pass> <sslmode> <tool> <args...>
run_pg() {
  local pass="$1" ssl="$2" tool="$3"; shift 3
  if command -v "$tool" >/dev/null 2>&1; then
    PGPASSWORD="$pass" PGSSLMODE="$ssl" "$tool" "$@"
  else
    $SAIL exec -T "$PG_SERVICE" env PGPASSWORD="$pass" PGSSLMODE="$ssl" "$tool" "$@"
  fi
}

# connection flags
ca() { printf -- '-h %s -p %s -U %s -d %s' "$1" "$2" "$3" "$4"; }

confirm() { # confirm <message> <db-name-to-type>
  [ "$ASSUME_YES" = "1" ] && return 0
  printf '\033[1;33m%s\033[0m ' "$1 [type '$2' to proceed]:" >&2
  local ans; read -r ans
  [ "$ans" = "$2" ] || die "aborted (confirmation did not match)"
}

# ---------------------------------------------------------------------------
cmd_export() {
  load_env
  local outdir="${1:-storage/db-etl/$(date +%Y%m%d-%H%M%S)}"
  mkdir -p "$outdir"
  log "Exporting $DB_DATABASE @ $DB_HOST -> $outdir"
  # shellcheck disable=SC2046
  log "1/3 full backup (custom format, exact restore) ..."
  run_pg "$DB_PASSWORD" "$DB_SSLMODE" pg_dump $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") \
    -Fc --no-owner --no-privileges > "$outdir/full.dump"
  # shellcheck disable=SC2046
  log "2/3 schema-only (reference) ..."
  run_pg "$DB_PASSWORD" "$DB_SSLMODE" pg_dump $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") \
    --schema-only --no-owner --no-privileges > "$outdir/schema.sql"
  # shellcheck disable=SC2046
  log "3/3 data-only, column-named INSERTs (structure-tolerant) ..."
  run_pg "$DB_PASSWORD" "$DB_SSLMODE" pg_dump $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") \
    --data-only --column-inserts --no-owner > "$outdir/data.sql"
  log "Done -> $outdir"
  ls -la "$outdir" >&2
  echo "$outdir"   # stdout: machine-readable path (used by safe-migrate)
}

cmd_safe_migrate() {
  load_env
  log "Pre-migration backup of $DB_DATABASE @ $DB_HOST ..."
  local dir; dir="$(cmd_export | tail -1)"
  log "Backup at: $dir"
  log "Running: sail artisan migrate $*"
  $SAIL artisan migrate "$@"
  log "Migration done. Rollback if needed:  ./scripts/db-etl.sh restore-full $dir"
}

cmd_import_data() {
  load_env
  local dir="${1:?usage: import-data <export-dir> [--truncate]}"; shift || true
  local truncate=0
  [ "${1:-}" = "--truncate" ] && truncate=1
  local data="$dir/data.sql"
  [ -f "$data" ] || die "no data.sql in $dir"
  confirm "LOAD data into live DB '$DB_DATABASE' @ $DB_HOST." "$DB_DATABASE"

  local pre=""
  if [ "$truncate" = "1" ]; then
    log "Building TRUNCATE for all public tables ..."
    # shellcheck disable=SC2046
    pre="$(run_pg "$DB_PASSWORD" "$DB_SSLMODE" psql $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") -At -c \
      "SELECT 'TRUNCATE TABLE '||string_agg(format('%I.%I',schemaname,tablename),', ')||' RESTART IDENTITY CASCADE;' FROM pg_tables WHERE schemaname='public';")"
  fi

  log "Importing (single transaction, FK/triggers deferred via session_replication_role) ..."
  # shellcheck disable=SC2046
  {
    echo "BEGIN;"
    echo "SET session_replication_role = replica;"
    [ -n "$pre" ] && echo "$pre"
    cat "$data"
    echo "SET session_replication_role = origin;"
    echo "COMMIT;"
  } | run_pg "$DB_PASSWORD" "$DB_SSLMODE" psql $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") \
        -v ON_ERROR_STOP=1 -q
  log "Import complete."
}

cmd_restore_full() {
  load_env
  local target="${1:?usage: restore-full <export-dir-or-full.dump>}"
  local dump="$target"; [ -d "$target" ] && dump="$target/full.dump"
  [ -f "$dump" ] || die "no full.dump at $dump"
  confirm "RESTORE (drops & recreates objects) into '$DB_DATABASE' @ $DB_HOST." "$DB_DATABASE"
  log "Restoring from $dump ..."
  # shellcheck disable=SC2046
  run_pg "$DB_PASSWORD" "$DB_SSLMODE" pg_restore $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") \
    --clean --if-exists --no-owner --no-privileges < "$dump"
  log "Restore complete."
}

cmd_clone() {
  load_env                                  # source = current .env
  local tfile="${1:?usage: clone <target-env-file>}"
  [ -f "$tfile" ] || die "missing target env file: $tfile"
  local THOST TPORT TDB TUSER TPASS TSSL
  THOST="$(envget DB_HOST "$tfile")"; TPORT="$(envget DB_PORT "$tfile")"
  TDB="$(envget DB_DATABASE "$tfile")"; TUSER="$(envget DB_USERNAME "$tfile")"
  TPASS="$(envget DB_PASSWORD "$tfile")"; TSSL="$(envget DB_SSLMODE "$tfile")"
  : "${THOST:?target DB_HOST missing}" "${TDB:?target DB_DATABASE missing}"
  TPORT="${TPORT:-5432}"; TSSL="${TSSL:-prefer}"
  [ "$THOST/$TDB" = "$DB_HOST/$DB_DATABASE" ] && die "source and target are identical"
  confirm "CLONE '$DB_DATABASE'@$DB_HOST  ->  '$TDB'@$THOST (overwrites target)." "$TDB"
  log "Streaming pg_dump (source) | pg_restore (target) ..."
  # shellcheck disable=SC2046
  run_pg "$DB_PASSWORD" "$DB_SSLMODE" pg_dump $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") \
      -Fc --no-owner --no-privileges \
  | run_pg "$TPASS" "$TSSL" pg_restore $(ca "$THOST" "$TPORT" "$TUSER" "$TDB") \
      --clean --if-exists --no-owner --no-privileges
  log "Clone complete -> '$TDB'@$THOST"
}

usage() {
  sed -n '2,33p' "$0" | sed 's/^# \{0,1\}//'
  cat >&2 <<'EOF'

FLAGS / ENV
  ENV_FILE=path   source env file (default: .env)
  ASSUME_YES=1    skip confirmation prompt (CI)
  PG_SERVICE=...  Sail service with pg client tools (default: pgsql)

EXAMPLES
  ./scripts/db-etl.sh export
  ./scripts/db-etl.sh safe-migrate                 # backup + sail artisan migrate
  ./scripts/db-etl.sh import-data storage/db-etl/20260601-120000
  ./scripts/db-etl.sh restore-full storage/db-etl/20260601-120000
  ./scripts/db-etl.sh clone .env.sail.example      # copy RDS -> local container db
EOF
}

case "${1:-help}" in
  export)        shift; cmd_export "$@";;
  safe-migrate)  shift; cmd_safe_migrate "$@";;
  import-data)   shift; cmd_import_data "$@";;
  restore-full)  shift; cmd_restore_full "$@";;
  clone)         shift; cmd_clone "$@";;
  help|-h|--help) usage;;
  *) err "unknown command: $1"; usage; exit 1;;
esac
