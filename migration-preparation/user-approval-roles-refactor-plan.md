# Safe Refactor Plan — User Approval & Role Assignment

> Generated: 2026-06-01
> Companion to: `user-approval-roles-rbac-map.md` (risk IDs R1–R11 referenced throughout)
> Goal: Refactor the user-approval / role-assignment system to make it consistent, centralized, and safe to extend — and add one new role — **without breaking existing users**.
> Status: **Plan only. No code in this document.**

---

## 0. Guiding Principles

1. **Backward-compatible first.** Every change in early phases must leave existing behavior identical. New abstractions wrap existing logic before anything is removed.
2. **Additive, then subtractive.** Introduce the centralized source of truth, migrate call sites to it, and only then delete the duplicated/hardcoded logic.
3. **Idempotent & reproducible.** Seeders/migrations must be safe to re-run on production (which already has data) and on fresh installs.
4. **One behavior change per PR.** Each phase is independently shippable and revertible.
5. **Data integrity > convenience.** Never rename or delete existing roles/permissions in-place; never retroactively change `email_verified_at`/`is_suspended` for existing users.

---

## 1. What Should Be Changed First (Phase 1 — Foundation, zero behavior change)

These are pure refactors that introduce a single source of truth without altering runtime behavior. Ship them before touching the approval flow or adding the role.

1. **Introduce a canonical role/permission registry** (addresses R3, R4, R8).
   - A `UserRole` enum (or constants class) holding every role's canonical name + naming convention decision.
   - Extend `config/permission.php` `role_names` to include **every** role (including `city_administrator`), so nothing references a bare string.
   - Decide the naming convention now (recommendation: keep existing Title Case for the 4 core roles, and standardize **new** roles as snake_case slugs — but document the rule explicitly so it is intentional, not accidental).

2. **Create a declarative role → permission map** (addresses R2, R10, recommendation #3).
   - A single data structure: `[roleName => [permissions...]]`, covering all current roles and their current permissions exactly as seeded today.
   - Build one **idempotent** `RolesAndPermissionsSeeder` (using `firstOrCreate` for roles/permissions and `syncPermissions`/`givePermissionTo`) that reproduces today's state. Register it in `DatabaseSeeder`.
   - Fold `CityAdministratorSeeder`'s role+permissions into this map so the unregistered-seeder bug (R2) disappears.

3. **Extract a single role-assignment service** (addresses R7, recommendation #4).
   - `UserRoleService::assignRole(User, roleId|roleName)` encapsulating the detach + assign + `forgetCachedPermissions()` pattern.
   - Do **not** yet swap call sites; just create and unit-test it in this phase, or swap call sites with byte-for-byte equivalent behavior.

4. **Replace hardcoded literals with the registry** at read-only sites first (addresses R4, R8):
   - `AuthServiceProvider::Gate::after` `'Super Administrator'` → config/enum.
   - `RegisteredUserController` `User::role('Super Administrator')` → config/enum.
   - `User::isCityAdmin/isCityAdministrator` `'city_administrator'` → config/enum.
   - `RoleSeeder` / `UserAndPermissionSeeder` literals → registry (or deprecate in favor of the new seeder).

**Exit criteria for Phase 1:** All tests green; a fresh `migrate:fresh --seed` produces an identical role/permission/user set to today (plus `city_administrator` now reliably present); no functional behavior changed.

---

## 2. What Should NOT Be Touched Yet

Defer these until the foundation is in place and verified. Touching them early maximizes blast radius.

- **The `users` table status columns** (`email_verified_at`, `is_suspended`). Do **not** add a `status`/`approved_at` column in Phase 1 — that is a separate, opt-in decision (Phase 4) with data-backfill implications.
- **The dual-portal session logic** (`home_url`, `EnsureLoginFrom*` middleware, `AuthenticateLoginAttempt` admin gate). Structurally load-bearing and out of scope for the approval/role refactor.
- **The existing role NAMES.** Do not rename `Super Administrator`, `Administrator`, `Author`, `Performer`, or `city_administrator` in the DB. Renaming breaks `model_has_roles`, hardcoded checks, and any external references. Only centralize *how they're referenced*.
- **Spatie permission internals / `config/permission.php` structural settings** (`enable_wildcard_permission`, cache, table names).
- **The Form-Builder mapping schema** (`form_mapping_rules` shape, `group='role'`). Reuse it; don't restructure it.
- **`Gate::after` super-admin bypass behavior** (keep the behavior; only swap the literal for config).
- **OAuth / 2FA / password-reset flows** — unrelated to role/approval.
- **Lunar/e-commerce and Booking module internals** beyond the permissions they consume.

---

## 3. How to Introduce the New Role Safely (Phase 3)

> Prerequisite: Phase 1 foundation merged and deployed. Use the now-centralized registry + seeder + assignment service.

1. **Define identity once.** Add the new role to the `UserRole` registry/enum and to the declarative role→permission map with its exact permission set. Choose the name per the documented convention.
2. **Decide admin access explicitly.** If it needs the `/admin` panel, it MUST include `system.dashboard` (otherwise login + the entire admin route group reject it). If it's a frontend role, ensure it does NOT get `system.dashboard`.
3. **Add new permissions (if any)** to the permission map; they get created by the idempotent seeder + a forward migration/seed for existing environments.
4. **Seed idempotently** via the consolidated seeder (`firstOrCreate`), so production gains the role/permissions without disturbing existing rows.
5. **Wire enforcement** using existing patterns: `can:<permission>` middleware on new routes and/or policy methods extending `BasePermissionPolicy`. Avoid new bespoke `hasRole('...')` checks — prefer permission checks.
6. **Site/branch scoping decision** (addresses R9): if the new role is site-scoped like City Admin, either (a) reuse `city_user`/`adminCities()` as-is, or (b) generalize to a role-agnostic "user ↔ site" assignment. For a first safe rollout, **reuse** the existing pivot and document it; generalization is a later, separate refactor.
7. **Make it assignable everywhere it should be** (addresses R11): verify it appears in `UserService::getRoleOptions` and `AutomateUserCreationService::getRoleOptions` (note the `withoutSuperAdmin` vs `withoutAdmin` difference) and is allowed by `UserStoreRequest::getRoleIds` for the intended actors.
8. **Frontend**: surface the role in `User/Create.vue`, `User/Edit.vue`, role filters, and any role-specific UI (e.g. `CitySelect.vue` if site-scoped).
9. **Public profile (if performer-like)**: grant `public_page.profile` and optionally add a `profile-<role-slug>.blade.php` view.

**Roll out behind low risk:** seed the role first (no users assigned → no behavior change), verify in staging, then enable assignment in the UI.

---

## 4. How to Avoid Breaking Existing Users

- **Never mutate existing role/permission rows destructively.** Use `firstOrCreate` + `syncPermissions`; avoid `truncate`/`delete` in seeders that run on production.
- **Preserve existing role assignments.** The refactor must not detach/reassign any `model_has_roles` rows for existing users. The new `UserRoleService` is used for *new* assignments only; existing pivots stay untouched.
- **Keep the "approved/active" semantics identical** unless Phase 4 is explicitly chosen. If a `status` column is later added, **backfill** it from current reality:
  - verified + not suspended + has role → "active";
  - unverified → "pending";
  - suspended → "suspended".
  Default new column so existing users are never accidentally locked out.
- **Don't change wildcard semantics.** `enable_wildcard_permission` stays true; Administrator keeps `*.*` + `system.*`.
- **Fix the Form-Builder email-verification gap (R5) carefully.** Changing it affects *future* form-approved users only. Decide intentionally:
  - Option A: explicitly `verifiyEmail()` on form-approval (matches admin-create behavior), or
  - Option B: leave unverified and send a verification email.
  Do **not** retroactively verify existing users.
- **Permission cache.** After any role/permission change, ensure `spatie.permission.cache` is cleared (the `RoleObserver` already does this on role writes; seeders should call `forgetCachedPermissions()`/`php artisan permission:cache-reset`).
- **Deploy ordering.** Run migrations/seeders before code that references new permissions; gate new UI behind the role existing.

---

## 5. Suggested Database Migration Steps (only if needed)

Migrations are **optional** and depend on how far you take the refactor.

**Required for the new role (minimal):**
- Usually **none** — roles/permissions are data, created idempotently by the seeder. For existing environments, add a small **forward-only data migration or `php artisan db:seed --class=RolesAndPermissionsSeeder`** step in the deploy/release script so prod gains the new role + permissions.

**Optional — only if you adopt an explicit approval state (Phase 4):**
1. `add_status_to_users` migration: nullable `status` (string/enum) or `approved_at` (timestamp nullable) + `approved_by` (nullable FK to users). Nullable + sane default to avoid locking anyone out.
2. **Backfill migration/command** (idempotent, chunked): set status from current `email_verified_at` / `is_suspended` / role presence (mapping in §4).
3. Add an index on `status` if it will be filtered in admin listings.

**Optional — only if you generalize site/branch scoping (deferred):**
- New `user_site`/generalized pivot, or repurpose `city_user`. Provide a data migration copying `city_user` rows. **Do not drop `city_user` in the same release** — keep it until all readers are migrated.

> Rule: all migrations are **forward-only and additive**; no column drops/renames in the same release that introduces the new behavior.

---

## 6. Suggested Tests to Add or Update

**Phase 1 (foundation):**
- Unit test for `UserRole` registry/config (every role name resolvable; no stray literals).
- Seeder test: fresh seed produces exactly the expected roles + role→permission map (including `city_administrator`). Re-running the seeder is idempotent (no duplicates).
- `UserRoleService::assignRole` unit tests: assign, reassign (detach old), assign-none (detach all), cache reset.

**Phase 3 (new role):**
- New `tests/Feature/RolePermission/<NewRole>PermissionTest.php` mirroring `UserPermissionTest` / `CityAdministratorTest`: which routes/abilities are allowed vs denied.
- Login test: new role can/can't reach `/admin` per its `system.dashboard` decision.
- Assignment tests: role appears in admin create/edit options and form-mapping options; `UserStoreRequest` accepts it for the right actors; assignment via `UserController@store/@update` and via `AutomateUserCreationController` both work.
- Site-scoping tests (if applicable): `city_user` assignment + `isCityAdmin`-style checks.

**Regression / "don't break existing users":**
- Existing role-permission tests (`tests/Feature/RolePermission/*`) must stay green unchanged.
- Test that existing users retain their roles after running the new seeder (no detach).
- `CheckSuspended`, `verified` middleware behavior unchanged for existing users.
- Form-approval flow test covering the email-verification decision (R5) — assert the chosen behavior.

**Phase 4 (only if approval state added):**
- Backfill correctness test (verified→active, unverified→pending, suspended→suspended).
- Default value test (new user gets correct status; nobody locked out).

---

## 7. Step-by-Step Implementation Plan (phased PRs)

**PR 1 — Centralize role/permission identity (no behavior change)**
1. Add `UserRole` enum/registry; extend `config/permission.php` `role_names` to cover all roles.
2. Swap read-only literals (`Gate::after`, `RegisteredUserController`, `User::isCityAdmin*`) to config/enum.
3. Add unit tests for the registry. Verify full test suite green.

**PR 2 — Consolidated, idempotent seeder (fixes R2)**
1. Build declarative role→permission map reproducing today's state + `city_administrator`.
2. Implement `RolesAndPermissionsSeeder` (idempotent) and register in `DatabaseSeeder`.
3. Deprecate/forward old seeders to it (keep behavior identical).
4. Add seeder idempotency + correctness tests. Verify `migrate:fresh --seed` parity.

**PR 3 — Unify role assignment (fixes R7)**
1. Implement `UserRoleService::assignRole`.
2. Swap `UserController@store/@update` and `AutomateUserCreationService::assignRole` to use it (byte-equivalent behavior).
3. Add unit tests. Verify existing user/role tests green.

**PR 4 — Introduce the new role (Phase 3)**
1. Add role + permissions to the map/enum; new permissions seeded.
2. Wire enforcement (routes/policies via permissions).
3. Make assignable (role options, `UserStoreRequest`, frontend pages); reuse `city_user` if site-scoped.
4. Add the new role's permission/login/assignment tests.
5. Deploy: run seeder (role present, unassigned) → verify staging → enable UI assignment.

**PR 5 — (Optional) Fix Form-Builder verification gap (R5)**
1. Decide verify-now vs send-verification.
2. Implement for the form-approval path only; do not touch existing users.
3. Add/adjust the form-approval verification test.

**PR 6 — (Optional) Explicit approval state (Phase 4)**
1. Additive migration (`status`/`approved_at`, nullable + default).
2. Idempotent backfill from current reality.
3. Read the new column in admin listings/filters (don't yet enforce gating to avoid lockouts).
4. Backfill + default tests. Only later, switch enforcement to the explicit state once confidence is high.

**Cross-cutting for every PR:** clear permission cache on deploy, run the full RBAC test suite, and keep each PR independently revertible.

---

## 8. Sequencing & Risk Summary

| PR | Touches | Behavior change | Risk | Revertible |
|----|---------|-----------------|------|-----------|
| 1 | config/enum, read-only literals | None | Low | Yes |
| 2 | seeders | None (parity) | Low–Med (prod seed) | Yes |
| 3 | role-assignment call sites | None (equivalent) | Low | Yes |
| 4 | new role end-to-end | New role only | Med | Yes (unassign + leave seeded) |
| 5 | form-approval verify | Future form users | Med | Yes |
| 6 | new column + backfill | New, opt-in | Med–High | Forward-only; plan carefully |

**Do first:** PR 1 → PR 2 → PR 3 (foundation).
**Do not touch yet:** status columns, portal/session logic, existing role names, OAuth/2FA, Form-Builder schema (until PR 4+).
**New role:** PR 4, after foundation, seeded-but-unassigned first.

*End of plan.*
