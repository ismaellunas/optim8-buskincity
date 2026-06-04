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
#   export [out-dir]                 snapshot: full.dump + schema.sql + data.sql (slow on RDS)
#   export-minimal [out-dir]         snapshot: full.dump + schema.sql only (fast; enough for rollback)
#   safe-migrate [artisan args...]     minimal backup, then `sail artisan migrate ...` (everyday)
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
PG_CLIENT_IMAGE="${PG_CLIENT_IMAGE:-postgres:17-alpine}"  # client for remote RDS (must match server major)
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
  : "${DB_HOST:?DB_HOST missing in $ENV_FILE}"
  : "${DB_DATABASE:?DB_DATABASE missing}" "${DB_USERNAME:?DB_USERNAME missing}"
  DB_PORT="${DB_PORT:-5432}"
  DB_SSLMODE="$(envget DB_SSLMODE "$ENV_FILE")"
  # RDS and other remote Postgres hosts require TLS from external clients.
  if [[ -z "$DB_SSLMODE" || "$DB_SSLMODE" == "prefer" || "$DB_SSLMODE" == "disable" ]]; then
    if [[ "$DB_HOST" == *amazonaws.com* || "$DB_HOST" == *rds.amazonaws.com* ]]; then
      DB_SSLMODE="require"
    elif [[ "$DB_HOST" != "pgsql" && "$DB_HOST" != "localhost" && "$DB_HOST" != "127.0.0.1" ]]; then
      DB_SSLMODE="require"
    else
      DB_SSLMODE="${DB_SSLMODE:-prefer}"
    fi
  fi
}

# true when host is outside the local Sail network (RDS, etc.)
is_remote_host() {
  local h="$1"
  [[ "$h" != "pgsql" && "$h" != "localhost" && "$h" != "127.0.0.1" ]]
}

# run a pg client tool. For remote hosts uses a Docker PG 17 client so pg_dump
# major version matches RDS (local Homebrew/Sail images are often PG 15).
#   run_pg <pass> <sslmode> <host> <tool> <args...>
run_pg() {
  local pass="$1" ssl="$2" host="$3" tool="$4"; shift 4
  if is_remote_host "$host"; then
    command -v docker >/dev/null 2>&1 || die "remote DB backup requires Docker (pg_dump must match server major version; RDS is PG 17)"
    docker run --rm -i \
      -e PGPASSWORD="$pass" -e PGSSLMODE="$ssl" \
      "$PG_CLIENT_IMAGE" "$tool" "$@"
  elif command -v "$tool" >/dev/null 2>&1 && [[ "$host" != "pgsql" ]]; then
    PGPASSWORD="$pass" PGSSLMODE="$ssl" "$tool" "$@"
  else
    $SAIL exec -T "$PG_SERVICE" env PGPASSWORD="$pass" PGSSLMODE="$ssl" "$tool" "$@"
  fi
}

# suppress known noisy pg_dump circular-FK warnings while keeping other warnings/errors
filter_pg_dump_stderr() {
  local err_file="$1"
  local saw_circular=0
  if grep -q "^pg_dump: warning: there are circular foreign-key constraints" "$err_file"; then
    saw_circular=1
  fi
  if [ "$saw_circular" -eq 1 ]; then
    log "pg_dump reported circular-FK warnings (known schema cycles; safe for this workflow)."
  fi
  awk '
    BEGIN { suppress = 0 }
    /^pg_dump: warning: there are circular foreign-key constraints/ { suppress = 1; next }
    /^pg_dump: warning: there are circular foreign-key constraints on this table:/ { suppress = 1; next }
    /^pg_dump: detail:/ && suppress { next }
    /^pg_dump: hint: You might not be able to restore the dump without using --disable-triggers or temporarily dropping the constraints\./ && suppress { next }
    /^pg_dump: hint: Consider using a full dump instead of a --data-only dump to avoid this problem\./ && suppress { next }
    { suppress = 0; print }
  ' "$err_file" >&2
}

# connection flags
ca() { printf -- '-h %s -p %s -U %s -d %s' "$1" "$2" "$3" "$4"; }

confirm() { # confirm <message> <db-name-to-type>
  [ "$ASSUME_YES" = "1" ] && return 0
  printf '\033[1;33m%s\033[0m ' "$1 [type '$2' to proceed]:" >&2
  local ans; read -r ans
  [ "$ans" = "$2" ] || die "aborted (confirmation did not match)"
}

# human-readable size for progress logs
file_size_h() {
  local f="$1"
  [ -f "$f" ] || { echo "0 B"; return; }
  local bytes
  bytes=$(stat -f%z "$f" 2>/dev/null || stat -c%s "$f" 2>/dev/null || echo 0)
  if [ "$bytes" -lt 1024 ]; then echo "${bytes} B"
  elif [ "$bytes" -lt 1048576 ]; then echo "$((bytes / 1024)) KB"
  elif [ "$bytes" -lt 1073741824 ]; then echo "$((bytes / 1048576)) MB"
  else echo "$((bytes / 1073741824)) GB"
  fi
}

# run pg_dump to a file with periodic size updates (remote RDS dumps look "stuck" otherwise)
run_pg_dump_to_file() {
  local outfile="$1" errfile="$2" label="$3"
  shift 3
  log "$label (in progress — remote dumps can take several minutes) ..."
  local monitor_pid=""
  (
    while true; do
      sleep 20
      if [ -f "$outfile" ]; then
        log "$label: $(file_size_h "$outfile") written so far ..."
      fi
    done
  ) &
  monitor_pid=$!
  if ! run_pg "$DB_PASSWORD" "$DB_SSLMODE" "$DB_HOST" pg_dump "$@" > "$outfile" 2> "$errfile"; then
    kill "$monitor_pid" 2>/dev/null || true
    wait "$monitor_pid" 2>/dev/null || true
    return 1
  fi
  kill "$monitor_pid" 2>/dev/null || true
  wait "$monitor_pid" 2>/dev/null || true
  log "$label: done ($(file_size_h "$outfile"))"
}

# EXPORT_MODE=minimal|full (minimal skips slow data.sql — enough for restore-full rollback)
cmd_export() {
  load_env
  local outdir="${1:-storage/db-etl/$(date +%Y%m%d-%H%M%S)}"
  local mode="${EXPORT_MODE:-full}"
  mkdir -p "$outdir"
  log "Exporting $DB_DATABASE @ $DB_HOST -> $outdir (mode=$mode)"
  if is_remote_host "$DB_HOST"; then
    log "Using Docker client $PG_CLIENT_IMAGE (remote PG requires matching pg_dump major version)"
  fi
  # shellcheck disable=SC2046
  local full_err="$outdir/.full.stderr.log"
  if ! run_pg_dump_to_file "$outdir/full.dump" "$full_err" "1/3 full backup" \
    $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") \
    -Fc --no-owner --no-privileges; then
    sed -n '1,200p' "$full_err" >&2
    rm -f "$full_err"
    die "full backup export failed"
  fi
  filter_pg_dump_stderr "$full_err"
  rm -f "$full_err"
  # shellcheck disable=SC2046
  local schema_err="$outdir/.schema.stderr.log"
  if ! run_pg_dump_to_file "$outdir/schema.sql" "$schema_err" "2/3 schema-only" \
    $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") \
    --schema-only --no-owner --no-privileges; then
    sed -n '1,200p' "$schema_err" >&2
    rm -f "$schema_err"
    die "schema-only export failed"
  fi
  filter_pg_dump_stderr "$schema_err"
  rm -f "$schema_err"
  if [ "$mode" = "minimal" ]; then
    log "Skipping 3/3 data-only export (not needed for safe-migrate; use restore-full on full.dump)."
    log "For structure-tolerant reimport run: EXPORT_MODE=full ./scripts/db-etl.sh export"
  else
    # shellcheck disable=SC2046
    local data_err="$outdir/.data-only.stderr.log"
    if ! run_pg_dump_to_file "$outdir/data.sql" "$data_err" "3/3 data-only (slow)" \
      $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") \
      --data-only --column-inserts --no-owner; then
      sed -n '1,200p' "$data_err" >&2
      rm -f "$data_err"
      die "data-only export failed"
    fi
    filter_pg_dump_stderr "$data_err"
    rm -f "$data_err"
    [ -s "$outdir/data.sql" ] || die "backup failed: $outdir/data.sql is empty"
  fi
  [ -s "$outdir/full.dump" ] || die "backup failed: $outdir/full.dump is empty (check DB_SSLMODE=require for RDS; PG_CLIENT_IMAGE=postgres:17-alpine)"
  [ -s "$outdir/schema.sql" ] || die "backup failed: $outdir/schema.sql is empty"
  log "Done -> $outdir"
  ls -la "$outdir" >&2
  echo "$outdir"   # stdout: machine-readable path (used by safe-migrate)
}

cmd_export_minimal() {
  EXPORT_MODE=minimal cmd_export "$@"
}

cmd_safe_migrate() {
  load_env
  log "Pre-migration backup of $DB_DATABASE @ $DB_HOST (minimal: full.dump only, no slow data.sql) ..."
  local dir; dir="$(EXPORT_MODE=minimal cmd_export | tail -1)"
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
    pre="$(run_pg "$DB_PASSWORD" "$DB_SSLMODE" "$DB_HOST" psql $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") -At -c \
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
  } | run_pg "$DB_PASSWORD" "$DB_SSLMODE" "$DB_HOST" psql $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") \
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
  run_pg "$DB_PASSWORD" "$DB_SSLMODE" "$DB_HOST" pg_restore $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") \
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
  run_pg "$DB_PASSWORD" "$DB_SSLMODE" "$DB_HOST" pg_dump $(ca "$DB_HOST" "$DB_PORT" "$DB_USERNAME" "$DB_DATABASE") \
      -Fc --no-owner --no-privileges \
  | run_pg "$TPASS" "$TSSL" "$THOST" pg_restore $(ca "$THOST" "$TPORT" "$TUSER" "$TDB") \
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
  PG_CLIENT_IMAGE=...  Docker image for pg_dump/psql against remote RDS (default: postgres:17-alpine)

EXAMPLES
  ./scripts/db-etl.sh export                       # full + schema + data.sql (data.sql is slow on RDS)
  ./scripts/db-etl.sh export-minimal               # full + schema only (rollback-safe, fast)
  ./scripts/db-etl.sh safe-migrate                 # minimal backup + sail artisan migrate
  ./scripts/db-etl.sh import-data storage/db-etl/20260601-120000
  ./scripts/db-etl.sh restore-full storage/db-etl/20260601-120000
  ./scripts/db-etl.sh clone .env.sail.example      # copy RDS -> local container db
EOF
}

case "${1:-help}" in
  export)         shift; cmd_export "$@";;
  export-minimal) shift; cmd_export_minimal "$@";;
  safe-migrate)   shift; cmd_safe_migrate "$@";;
  import-data)   shift; cmd_import_data "$@";;
  restore-full)  shift; cmd_restore_full "$@";;
  clone)         shift; cmd_clone "$@";;
  help|-h|--help) usage;;
  *) err "unknown command: $1"; usage; exit 1;;
esac
