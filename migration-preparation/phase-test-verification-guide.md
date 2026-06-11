# Phase Test & Verification Guide

> Single source of truth for **what to test** to confirm each phase is implemented successfully.
> Maps: implementation task → functional requirement (FR-*) → security vulnerability (V*) → acceptance criteria (AC*) → test method.
>
> Companion to `00-START-HERE-implementation-guide.md` (task list & guardrails) and `PROGRESS-LOG.md` (execution log).
> All requirement IDs reference `new-requirements-frs-and-refactor-plan.md` (FR-*, AC-*) and `new-requirements-security-scalability-and-phasing.md` (V*).
> Generated: 2026-06-05. Update status column as verification completes.

---

## 0. How to use this guide

1. Work **phase-by-phase** in the same order as implementation (0 → 7).
2. Each phase lists: automated test commands, DB/seed checks, and manual/UI steps.
3. A test case marked ⬜ **gap** has not been written yet — see §11 (T-TESTS) for the priority backlog.
4. **Pass criteria** for a phase: all automated tests green + all manual checks pass.
5. **Security checks** (IDOR/negative tests) are as important as happy-path tests — do not skip them.

---

## 1. Test environment prerequisites

Run once before any phase verification:

```bash
# 1. Start Sail
./vendor/bin/sail up -d

# 2. Run migrations (additive — safe to re-run)
./vendor/bin/sail artisan migrate

# 3. Seed roles/permissions (idempotent)
./vendor/bin/sail artisan db:seed --class=RolesAndPermissionsSeeder

# 4. Verify phpunit.xml targets local Sail DB (NOT RDS)
#    Check that this line exists in phpunit.xml:
#    <server name="DB_HOST" value="pgsql"/>

# 5. Baseline: phase-specific suites must be green before starting phase verification
#    (The full suite includes pre-existing failures in guest-route/DatabaseSeeder and
#    stale Fortify tests — see §12 for the commands that gate each phase.)
./vendor/bin/sail artisan test --filter=RolePermission
./vendor/bin/sail artisan test tests/Feature/CityAdministratorTest.php
./vendor/bin/sail artisan test tests/Feature/RolePermission/SpecialEventsAdminPermissionTest.php
./vendor/bin/sail artisan test tests/Feature/PitchLocationFkTest.php
./vendor/bin/sail artisan test tests/Feature/RoleApplicationTest.php
./vendor/bin/sail artisan test tests/Feature/LandingNavTest.php
./vendor/bin/sail artisan test tests/Feature/SpaceHierarchyTest.php
./vendor/bin/sail artisan test tests/Feature/EventsCalendarSearchTest.php
./vendor/bin/sail artisan test --filter=PerformerCancellation
```

---

## 2. Phase 0 — Role/permission foundation

**Tasks:** T0.1 (canonical role registry), T0.2 (idempotent seeder), T0.3 (unified role assignment service)
**Status in progress log:** ✅ ALL DONE (verified on RDS 2026-06-02)
**Fixes:** V8 (hardcoded roles / orphaned seeder), R2, R4, R8

### 2.1 Automated tests

```bash
./vendor/bin/sail artisan test --filter=RolePermission
./vendor/bin/sail artisan test tests/Feature/CityAdministratorTest.php
```

| Test file | Cases | What it verifies | Requirement |
|---|---|---|---|
| `tests/Feature/RolePermission/CategoryPermissionTest.php` | 12 | Category CRUD permission gates still intact (regression) | — |
| `tests/Feature/RolePermission/MediaPermissionTest.php` | 20 | Media CRUD permission gates (regression) | — |
| `tests/Feature/RolePermission/PagePermissionTest.php` | 12 | Page CRUD permission gates (regression) | — |
| `tests/Feature/RolePermission/PostPermissionTest.php` | 12 | Post CRUD permission gates (regression) | — |
| `tests/Feature/RolePermission/UserPermissionTest.php` | 14 | User CRUD + Super Admin self-protection | — |
| `tests/Feature/CityAdministratorTest.php` | 7 | City Admin login redirect, role assignment, city event scoping | FR-CA, V8, V9 |

**Expected result:** all green, zero failures.

### 2.2 DB/seed verification

```bash
# Check all 5 roles exist with expected permissions
./vendor/bin/sail artisan tinker --execute="
\Spatie\Permission\Models\Role::with('permissions')->get()->each(function(\$r) {
    echo \$r->name . ': ' . \$r->permissions->pluck('name')->join(', ') . PHP_EOL;
});"

# Idempotency: run seeder twice, confirm no errors
./vendor/bin/sail artisan db:seed --class=RolesAndPermissionsSeeder
./vendor/bin/sail artisan db:seed --class=RolesAndPermissionsSeeder
```

**Expected:** 5 roles — `Super Administrator`, `Administrator`, `Author`, `Performer`, `city_administrator` — with permissions matching `RolesAndPermissionsSeeder`. Second run produces no errors and no duplicate rows.

### 2.3 Manual checks

| Check | How | Expected | Requirement |
|---|---|---|---|
| Role registry resolves | Tinker: `config('permission.role_names.city_admin')` | Returns `'city_administrator'` | V8 |
| `UserRole` enum covers all 5 roles | Tinker: `\App\Enums\UserRole::values()` | Array with all 5 role strings | T0.1 |
| `Gate::after` uses config, not literal | Read `app/Providers/AuthServiceProvider.php` | Uses `config('permission.super_admin_role')`, not `'Super Administrator'` | V8 |
| `UserRoleService` is the only assign path | Grep for `->assignRole(` outside `UserRoleService` | No rogue `assignRole` call sites | T0.3 |

---

## 3. Phase 1 — Generalize scope + City Admin formalization

**Tasks:** T1.1 (`user_scope` table + backfill), T1.2 (`SpaceEventPolicy` registered + Administrator global scope), T1.3 (`system.dashboard` removed for City Admin)
**Status in progress log:** 🟢 CODE COMPLETE — requires DB migration + re-seed
**Fixes:** V7 (unregistered policy), V8 (dead-code branches), V9 (Administrator scope)

### 3.1 Migration + seed

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed --class=RolesAndPermissionsSeeder

# Confirm table exists
./vendor/bin/sail artisan tinker --execute="
echo Schema::hasTable('user_scope') ? 'user_scope: YES' : 'user_scope: MISSING';"
```

### 3.2 Automated tests

```bash
./vendor/bin/sail artisan test tests/Feature/CityAdministratorTest.php
./vendor/bin/sail artisan test --filter=RolePermission
```

| Test case | What it verifies | Requirement |
|---|---|---|
| `city_admin_can_view_events_in_their_city` | Scoped events visible to City Admin | FR-CA, V9 |
| `city_admin_cannot_view_events_in_other_cities` | Cross-city events denied | V7, V9 |
| `city_administrator_can_log_in_via_admin_login_without_dashboard_permission` | Login lands on Spaces (not dashboard) | OQ14, T1.3 |

### 3.3 Scope helper checks

```bash
./vendor/bin/sail artisan tinker --execute="
\$role = config('permission.role_names.city_admin');
\$user = \App\Models\User::role(\$role)
    ->whereHas('userScopes', fn (\$q) => \$q->where('role', \$role)->where('scope_type', 'city'))
    ->first()
    ?? \App\Models\User::role(\$role)->first();
if (\$user) {
    \$scopeIds = \$user->scopeIdsFor(\$role, 'city');
    \$pivotIds = \$user->adminCities()->pluck('cities.id')->map(fn (\$id) => (int) \$id)->all();
    sort(\$scopeIds);
    sort(\$pivotIds);
    echo 'user id: ' . \$user->id . PHP_EOL;
    echo 'scopeIdsFor: ' . implode(', ', \$scopeIds) . PHP_EOL;
    echo 'adminCities: ' . implode(', ', \$pivotIds) . PHP_EOL;
    echo 'match: ' . (\$scopeIds === \$pivotIds ? 'YES' : 'NO') . PHP_EOL;
    if (! empty(\$scopeIds)) {
        echo 'inScope: ' . (\$user->inScope('city', \$scopeIds[0], \$role) ? 'YES' : 'NO') . PHP_EOL;
    } else {
        echo 'inScope: SKIP (no city scope rows — assign cities via admin UI or syncAdminCities)' . PHP_EOL;
    }
    echo 'user_scope rows: ' . \$user->userScopes()->where('role', \$role)->count() . PHP_EOL;
    echo 'city_user rows (dual-read): ' . \$user->adminCities()->count() . PHP_EOL;
} else { echo 'No city admin user found — create one in admin or approve an application first.'; }"
```

**Expected:** `scopeIdsFor` and `adminCities` return matching city IDs (`match: YES`); `inScope` = YES when cities are assigned; both tables readable (dual-read intact).

> **API note:** `scopeIdsFor($role, $scopeType)` returns a plain `array` (not a Collection). `inScope($scopeType, $scopeId, $role)` — scope type comes first, role last.

### 3.4 Manual checks

| Check | Steps | Expected | Requirement |
|---|---|---|---|
| City Admin login redirect | Log in as a City Admin user | Lands on Spaces/Locations page — NOT the main dashboard | OQ14, T1.3 |
| City Admin sidebar | Inspect admin sidebar after City Admin login | Shows location/pitch management; no full system menu | OQ14, V8 |
| Administrator global scope | Log in as Administrator; open Spaces for a city with no `city_user` row for that admin | Can view/edit (not denied) | FR-ADMIN-1, V9 |
| `SpaceEventPolicy` registered | Tinker: `app(\App\Policies\SpaceEventPolicy::class)` | No null/exception — policy is bound | V7 |
| `city_user` dual-read intact | Tinker: `User::role('city_administrator')->first()->cities()->count()` | Returns > 0 (backwards-compatible read) | T1.1 guardrail |

---

## 4. Phase 2 — Special Events Admin role

**Tasks:** T2.1 (seed `special_events_admin`), T2.2 (assignable in UI + `user_scope`-only writing)
**Status in progress log:** 🟢 CODE COMPLETE — tests written but not yet run (Docker was down)
**Fixes:** FR-SE-1, FR-ACCT-2/5/6; V8 (new role in registry)

### 4.1 Automated tests

```bash
./vendor/bin/sail artisan test tests/Feature/RolePermission/SpecialEventsAdminPermissionTest.php
```

| Test case | What it verifies | Requirement |
|---|---|---|
| `the_role_is_seeded_and_assignable` | Role exists in DB; can be assigned to a user | FR-ACCT-6, OQ12 |
| `it_has_its_scoped_permissions` | `special_events.manage`, `product.add`, `public_page.profile` granted | FR-SE-1, OQ14 |
| `it_does_not_have_full_dashboard_access` | `system.dashboard` NOT granted | OQ14, FR-ADMIN, V8 |
| `assigning_cities_writes_user_scope_not_city_user` | SE Admin scope writes to `user_scope` only — never `city_user` | OQ10, T2.2 |
| `many_special_events_admins_can_share_a_city` | Two SE Admins for the same city both persist (no uniqueness block) | FR-ACCT-5, OQ10 |
| `assigned_scope_cities_resolves_from_user_scope` | `assignedScopeCities()` returns correct cities for SE Admin | T2.2 |
| `the_role_appears_in_the_admin_role_options` | Role appears in the user-creation role dropdown | FR-ACCT-6, T2.2 |

**Expected:** all 7 green.

### 4.2 DB/seed check

```bash
./vendor/bin/sail artisan tinker --execute="
\$r = \Spatie\Permission\Models\Role::where('name','special_events_admin')->with('permissions')->first();
echo \$r
    ? 'FOUND — perms: ' . \$r->permissions->pluck('name')->join(', ')
    : 'ROLE MISSING — run RolesAndPermissionsSeeder';"
```

**Expected:** role found; permissions = `special_events.manage`, `product.add`, `public_page.profile`; `system.dashboard` absent.

### 4.3 Manual UI checks

| Check | Steps | Expected | Requirement |
|---|---|---|---|
| SE Admin in role dropdown | Admin → Users → Create | "Special Events Admin" appears in role selector | FR-ACCT-6 |
| City assignment panel shown for SE Admin | Assign SE Admin role, inspect form | City multi-select panel visible (same panel as City Admin) | T2.2 |
| Scope written to `user_scope` only | Assign cities to SE Admin, save, then check DB | `user_scope` has rows; `city_user` does NOT | OQ10 |
| No dashboard for SE Admin | Log in as SE Admin | No full admin dashboard; redirected to appropriate limited page | OQ14 |
| Headline rendering | Check role label in UI | Displays "Special Events Admin" (not raw `special_events_admin`) | T2.2 |

---

## 5. Phase 3 — Pitch location FKs + server-side scope

**Tasks:** T3.1 (`locations` table + `lunar_products` FK columns + backfill), T3.2 (server-side scope validation), T3.3 (`Space.city_id` persisted on create/update)
**Status in progress log:** 🟢 CODE COMPLETE — migrations must run; tests written but not yet run
**Fixes:** V1 (pitch city IDOR), V3 (mass-assignment on type/city/cities sync)

### 5.1 Migration + schema check

```bash
./vendor/bin/sail artisan migrate

./vendor/bin/sail artisan tinker --execute="
\$checks = [
    'locations table'            => Schema::hasTable('locations'),
    'lunar_products.city_id'     => Schema::hasColumn('lunar_products','city_id'),
    'lunar_products.location_id' => Schema::hasColumn('lunar_products','location_id'),
    'lunar_products.is_special_event' => Schema::hasColumn('lunar_products','is_special_event'),
    'spaces.city_id'             => Schema::hasColumn('spaces','city_id'),
];
foreach(\$checks as \$label => \$ok) {
    echo (\$ok ? 'OK' : 'MISSING') . ' — ' . \$label . PHP_EOL;
}"
```

**Expected:** all 5 lines show `OK`.

### 5.2 Automated tests

```bash
./vendor/bin/sail artisan test tests/Feature/PitchLocationFkTest.php
```

| Test case | What it verifies | Requirement |
|---|---|---|
| `location_service_creates_a_canonical_location_for_a_city` | `LocationService` creates a `locations` row correctly linked to a `cities` row | FR-PITCH-2, FR-CA-3 |
| `scoped_city_ids_include_city_admin_scope` | `User::scopeIdsFor('city_administrator', 'city')` returns the admin's cities | V1, V3 |
| `globally_scoped_admin_has_no_city_restriction` | Administrator gets all cities / is not restricted | FR-ADMIN-1, V9 |
| `products_table_has_location_fk_columns` | Migration applied; columns exist in DB | T3.1 schema |
| `empty_space_id_scope_returns_no_spaces` | Empty scope array → zero spaces returned (not all spaces) | V3 |

**Expected:** all 5 green.

### 5.3 Security / IDOR tests (manual — critical, do not skip)

These verify the V1 and V3 mitigations that prevent cross-city data injection.

| Check | Steps | Expected | Requirement |
|---|---|---|---|
| Out-of-scope city rejected on pitch save | As City Admin for City A, POST `ProductEventController@update` with `city_id` = City B's ID | 422 or 403; pitch not saved | V1, 6.3 AC2 |
| Out-of-scope city rejected on Space create | As City Admin for City A, POST `SpaceController@store` with `parent_id` under City B | 422 or 403 | V3, 6.6 AC4 |
| `type_id` restricted on Space create | As City Admin, POST `SpaceStoreRequest` with `type_id` = Country-type ID | 422 | V3 |
| `city_id` validated on SpaceEvent | POST `SpaceEventController@update` with an arbitrary `city_id` | 422 | V3 |
| Arbitrary `cities` sync blocked | `CityUserController@update` with city IDs outside the actor's scope | 422 or 403 | V3 |

### 5.4 Manual checks

| Check | Steps | Expected | Requirement |
|---|---|---|---|
| Space `city_id` persisted | Create a child Space under a City node | `spaces.city_id` populated with the parent City's ID | FR-CA-3, T3.3 |
| Backfill ran | Tinker: count child Spaces under city nodes where `city_id IS NULL` | 0 (backfill complete) | T3.3 |
| `Location` model accessible | Tinker: `\App\Models\Location::first()` | Returns a row, or null if no pitches exist yet (both OK) | T3.1 |
| Pitch `city_id` backfill | Tinker: `\Lunar\Models\Product::whereNotNull('city_id')->count()` | > 0 if existing pitches have city meta | T3.1 |

---

## 6. Phase 4 — Atomic pitch save

**Tasks:** T4.1 (single transactional pitch save; collapse two-step to one; JSON response; FK location)
**Status in progress log:** 🟢 CODE COMPLETE — manual UI verification required
**Fixes:** V4 (pitch-save partial commit), FR-PITCH-7/8

> No automated test covers this phase yet (⬜ gap — see §11). All verification is manual UI.

### 6.1 Manual UI verification

| Test case | Steps | Expected | Requirement |
|---|---|---|---|
| Happy path — create with all fields | As City Admin: open Create Pitch, fill name, city, location, timeslot duration, pitch date range, timezone, schedule. Save. | Pitch appears in list; all fields (incl. city/country/location) are pre-populated when you re-open it. No "Oops". | FR-PITCH-8, V4, 6.3 AC4 |
| Save with no ProductEvent windows | Create pitch; leave Events tab empty; save | Pitch saves successfully; no error | FR-PITCH-7, 6.3 AC5 |
| Rollback on failure | Submit pitch with a required field deliberately left blank | Nothing persisted — no orphaned pitch appears in list | FR-PITCH-8, V4 |
| Field-level error surfaced | Submit with e.g. invalid schedule times | Specific field error shown — not a generic "Oops" | FR-PITCH-8 |
| City/country saved atomically | Create valid pitch; open it in edit | City and country are pre-populated (not empty) | FR-PITCH-2, V4 |
| No "Oops" on valid save | End-to-end valid pitch create | Success response; redirected or success message shown | V4 |

### 6.2 Regression

```bash
# Ensure existing booking and approval flows not broken
./vendor/bin/sail artisan test --filter=Booking
./vendor/bin/sail artisan test tests/Feature/RoleApplicationTest.php
```

---

## 7. Phase 5 — Application → Approval workflow

**Tasks:** T5.1 (public `/apply` form + `role_applications` entity + branding upload + reCAPTCHA/throttle), T5.2 (transactional approve/reject + one-per-city index + replace modal)
**Status in progress log:** ✅ DONE — `RoleApplicationTest` (5 cases) green 2026-06-04
**Fixes:** V2 (unverified approvals), V5 (one-per-city race), V6 (upload), FR-ACCT-1/2/3/4/5

### 7.1 Automated tests

```bash
./vendor/bin/sail artisan test tests/Feature/RoleApplicationTest.php
```

| Test case | What it verifies | Requirement |
|---|---|---|
| `guest_can_submit_a_city_admin_application` | Public `/apply` persists the application + branding | FR-ACCT-1, V6, 6.1 AC1 |
| `administrator_can_approve_and_provision_city_admin` | Approve: role + scope + email verified + city page provisioned in one transaction | FR-ACCT-3, V2, 6.1 AC1 |
| `approving_city_admin_application_replaces_existing_admin_when_confirmed` | Replace flow (OQ3): existing City Admin replaced after confirmation modal | FR-ACCT-4, OQ3, 6.1 AC3 |
| `protected_admin_email_is_rejected_on_approval` | Protected email domains cannot be minted as admins | V2, R5, 6.1 AC5 |
| `many_special_events_admins_are_allowed_for_one_city` | SE Admin many-per-city: no unique constraint blocks second approval | FR-ACCT-5, OQ10, 6.2 AC1 |

**Expected:** all 5 green.

### 7.2 Test gaps for this phase (priority: HIGH — write these next)

| Missing test | Description | Acceptance criteria |
|---|---|---|
| Concurrent city-admin approval race | Simulate two approvals for the same city processed simultaneously; verify only one succeeds | 6.1 AC4, V5 |
| Upload: bad MIME rejected | POST `/apply` with a non-image file for the logo field | 422 (V6) |
| Upload: oversized file rejected | POST `/apply` with a file exceeding the max size | 422 (V6) |
| Throttle / reCAPTCHA | Exceed submission rate on `/apply` | 429 (V6) |

### 7.3 Manual / staging checks

| Check | Steps | Expected | Requirement |
|---|---|---|---|
| Public apply form — City Admin | Navigate to `/apply?role=city_administrator` | Form renders with city selector and branding upload fields | FR-ACCT-1 |
| Public apply form — SE Admin | Navigate to `/apply?role=special_events_admin` | Form renders | FR-ACCT-2 |
| Admin review page | Log in as Administrator/Super Admin → `/admin/role-applications` | Pending applications listed | FR-ACCT-3 |
| Replacement confirmation modal | Approve for a city that already has a City Admin | Confirmation modal shown before proceeding | OQ3, 6.1 AC3 |
| Reject flow | Click Reject, enter reason | Application marked `rejected`; no role granted; reason recorded | 6.1 AC2 |
| Approved user email verified | Check `users.email_verified_at` in DB for newly approved user | Not null | V2, 6.1 AC1 |
| Approved user scope set | Check `user_scope` table for approved user | Row with correct role + city scope present | FR-ACCT-3, V2 |
| Approved City Admin branding | Inspect city Space after approval | Logo/cover/text fields populated from the application branding | FR-ACCT-3, 6.1 AC1 |
| Reject — no role leak | Reject an application; check user roles | No role/scope was granted | 6.1 AC2 |

---

## 8. Phase 6 — Pitch UX + 14-day Special Events rule

**Tasks:** T6.1 (unified create form + renamed/reordered fields + single timezone + gallery disabled), T6.2 (14-day cap validation + visible-vs-bookable decoupled)
**Status in progress log:** 🟢 CODE COMPLETE — manual verification required
**Fixes:** FR-PITCH-3/5/6/9/10, FR-SE-2/3

> No automated tests for UI label/order changes — all T6.1 verification is manual.

### 8.1 T6.1 — Pitch form fields and labels (manual)

| Test case | Steps | Expected | Requirement |
|---|---|---|---|
| All fields visible before first save | Open Create Pitch (do NOT save first) | Location, timeslot duration, pitch date range, timezone, and schedule all visible immediately | FR-PITCH-3, 6.3 AC1 |
| Field order swapped | Inspect create form field order | "Timeslot duration" appears BEFORE "Pitch date range" | FR-PITCH-5 |
| "Duration" renamed | Read create form label | Shows "Timeslot duration" (not "Duration") | FR-PITCH-5 |
| "Weekly hours" renamed | Read create form label | Shows "Weekly days and hours" (not "Weekly hours") | FR-PITCH-6 |
| Weekly hours help copy | Read help text on "Weekly days and hours" | "Use this option to manually override day(s)/hours within the pitch date range" | FR-PITCH-6 |
| Single timezone field | Count timezone fields on create and edit forms | Exactly one timezone field on each | FR-PITCH-9 |
| Timezone persists correctly | Create pitch, set timezone, save, re-open | Timezone is pre-populated with what was set | FR-PITCH-9 |
| Gallery disabled | Open create or edit pitch | No gallery uploader visible | FR-PITCH-10 |
| City selector scoped | Open Create Pitch as City Admin | City dropdown shows only their assigned cities | FR-PITCH-4, 6.3 AC2 |

### 8.2 T6.2 — 14-day cap + visibility/bookability (manual)

| Test case | Steps | Expected | Requirement |
|---|---|---|---|
| Cap rejects > 14 days | As SE Admin: create pitch, set ProductEvent window > 14 days (e.g. 15 days), save | Validation error shown; not saved | FR-SE-2, 6.3 AC6 |
| Cap accepts = 14 days | Set bookable window exactly 14 days, save | Accepted and saved | FR-SE-2, 6.3 AC6 |
| Cap accepts < 14 days | Set bookable window 7 days, save | Accepted and saved | FR-SE-2, 6.3 AC6 |
| Normal pitch — no cap | As City Admin: create pitch with 30-day window | Accepted (14-day cap only applies to SE pitches) | FR-SE-2 |
| SE pitch visible outside window | Create SE pitch with a window > 2 weeks in the future; browse the city page | Pitch visible in listing year-round | FR-SE-3, 6.3 AC7 |
| Not bookable outside window | Try to book a timeslot before or after the SE pitch's allocated window | Timeslot not offered / booking rejected | FR-SE-3, 6.3 AC7 |
| Bookable within window | Try to book during the SE pitch's window | Booking succeeds | FR-SE-3, 6.3 AC7 |

### 8.3 Test gaps for this phase (write these next)

| Missing test | Description | Acceptance criteria |
|---|---|---|
| 14-day cap — POST validation | `POST ProductEventController@update` with > 14d as SE Admin → 422 | 6.3 AC6, FR-SE-2 |
| 14-day boundary — exactly 14d | Exactly 14 days → 200 | 6.3 AC6 |
| Visible-not-bookable | SE pitch: `canBook` returns false outside window; pitch still rendered | 6.3 AC7, FR-SE-3 |

---

## 9. Phase 7 — Landing nav, hierarchy rules, map pins, search

**Tasks:** T7.1 (slug resolution), T7.2 (data-driven nav), T7.3 (type dropdown removed + hierarchy rules), T7.4 (map pins + event search)
**Status in progress log:** T7.2/T7.3/T7.4 ✅ DONE (automated tests green); T7.1 🟢 CODE COMPLETE (manual browse pending)

### 9.1 Automated tests

```bash
./vendor/bin/sail artisan test tests/Feature/LandingNavTest.php
./vendor/bin/sail artisan test tests/Feature/SpaceHierarchyTest.php
./vendor/bin/sail artisan test tests/Feature/EventsCalendarSearchTest.php
```

#### `LandingNavTest.php` — T7.2 (2 cases)

| Test case | What it verifies | Requirement |
|---|---|---|
| `landing_nav_builds_country_menus_with_city_children` | Nav generated data-driven from Space tree; country→city structure correct | FR-NAV-1, 6.5 AC1 |
| `landing_nav_skips_cities_without_published_pages` | Cities with no published page are omitted | FR-NAV-1, FR-NAV-2 |

#### `SpaceHierarchyTest.php` — T7.3 (4 cases)

| Test case | What it verifies | Requirement |
|---|---|---|
| `city_admin_pitch_is_inferred_when_created_under_a_city` | Type inferred from parent; no type dropdown needed | FR-CA-2, 6.6 AC1 |
| `city_admin_cannot_create_a_location_under_a_country` | Parent must be a city node — country parent rejected | FR-CA-2, 6.6 AC3 |
| `global_admin_infers_pitch_type_when_parent_is_a_city` | Administrator also gets type inferred | FR-ADMIN-1 |
| `hierarchy_service_rejects_city_under_city` | City sibling of a city is rejected | FR-CA-2, 6.6 AC3 |

#### `EventsCalendarSearchTest.php` — T7.4 (4 cases)

| Test case | What it verifies | Requirement |
|---|---|---|
| `country_service_normalizes_alpha3_to_alpha2` | Country code mismatch (2 vs 3 chars) resolved | FR-NAV-3 |
| `events_calendar_includes_published_space_events` | `space_event` type now returned by `availableTypes()` | FR-NAV-3 |
| `events_calendar_country_filter_matches_alpha2_for_alpha3_backed_rows` | Country filter works end-to-end with normalized codes | FR-NAV-3, 6.5 AC3 |
| `events_calendar_exposes_is_special_event_flag` | `is_special_event` flag available per row for pin coloring | FR-NAV-4, FR-SE-4 |

**Expected:** all 10 automated tests green.

### 9.2 Manual checks — T7.1 (slug resolution, browser required)

Requires a DB with populated Spaces (country → city → pitch nodes).

| Check | Steps | Expected | Requirement |
|---|---|---|---|
| Populated country page | Browse to a country with cities | Page renders; no 404; no unique-ID error | FR-NAV-2, 6.5 AC1 |
| Populated city page | Navigate into a city | Page renders; pitches listed | FR-NAV-2 |
| Populated pitch page | Navigate into a pitch | Page renders; events/timeslots listed | FR-NAV-2 |
| Empty city (no pitches) | Navigate to a city with zero pitches | Empty state shown — NOT a 404 | FR-NAV-2, 6.5 AC2 |
| Empty pitch (no events) | Navigate to a pitch with no events | Empty state shown — NOT a 404 | FR-NAV-2, 6.5 AC2 |
| No slug collision | Two pitches with similar names in different cities | Each resolves to its own distinct page | FR-NAV-2 (full-path fix) |

### 9.3 Manual checks — T7.2 (data-driven nav) and T7.4 (map + search)

| Check | Steps | Expected | Requirement |
|---|---|---|---|
| Country submenu auto-generated | Open header nav | Country-level items list their cities as data-driven submenus (not manually crafted CMS items) | FR-NAV-1, 6.5 AC1 |
| City → pitch drill-down | Click a city in the nav | City page lists upcoming pitches | FR-NAV-1 |
| Search — normal events returned | Use "Search Events"; select a city with normal pitches | Normal pitch events appear in results | FR-NAV-3, 6.5 AC3 |
| Search — special events returned | Use "Search Events"; select a city with SE pitches | SE pitches also appear in results | FR-NAV-3, FR-NAV-4 |
| Search — country code | Search with a country stored with alpha3 code | Results return (not empty) | FR-NAV-3 |
| Map — normal pitch pin | View map with a normal pitch | Standard/default marker color | FR-NAV-4, 6.5 AC4 |
| Map — SE pitch pin | View map with a special-events pitch | Distinct/different color marker | FR-NAV-4, FR-SE-4, 6.5 AC4 |
| Map — both types visible | Browse a city with both normal and SE pitches | Both marker types visible simultaneously | FR-NAV-4, 6.5 AC4 |

### 9.4 Manual checks — T7.3 (location form, no type dropdown)

| Check | Steps | Expected | Requirement |
|---|---|---|---|
| No type dropdown | Open Space/Location create form as any admin | No "Type" dropdown visible | FR-CA-2, 6.6 AC1 |
| Type inferred from parent | Create a location under a City node | Type automatically set to Location; no user input required | FR-CA-2 |
| City parent enforced | Create a location with a Country node as parent | Rejected with a clear validation error | FR-CA-2, 6.6 AC3 |
| Sibling city blocked | Attempt to create a Space that would be a sibling to an existing city | Rejected | FR-CA-2, 6.6 AC3 |
| Out-of-scope parent blocked | As City Admin for City A, create location with City B's node as parent | 403 / 422 | V3, 6.6 AC4 |

---

## 10. Cross-cutting — T-PERF-CANCEL (performer cancellation)

**Status in progress log:** ✅ DONE — `PerformerCancellationTest` green
**Fixes:** OQ4, FR-PERF-2

### 10.1 Automated test

```bash
./vendor/bin/sail artisan test --filter=PerformerCancellation
```

### 10.2 Manual checks

| Check | Steps | Expected | Requirement |
|---|---|---|---|
| Performer can cancel | Log in as Performer → bookings → cancel a booking | Booking marked `canceled`; removed from upcoming list | OQ4 |
| Slot freed after cancel | After cancel, check availability for the same time slot | Slot re-appears as available/bookable | OQ4, 6.4 AC3 |
| Event removed from city calendar | After cancel, view city calendar | Canceled event no longer shown | OQ4 |
| Other bookings unaffected | Cancel one booking; inspect remaining bookings | Other bookings still active; no collateral cancellation | OQ4 regression |

---

## 11. T-TESTS — Remaining test gaps

The `00-START-HERE` guide lists these under `T-TESTS` (SEC doc §9 "Suggested tests"). Write them in priority order.

| Priority | Test description | Suggested file | Acceptance criteria |
|---|---|---|---|
| **HIGH** | IDOR: out-of-scope `city_id` rejected on pitch save | `tests/Feature/PitchScopedSaveTest.php` | 6.3 AC2, V1 |
| **HIGH** | Atomic save rollback: any failure → nothing persisted | `tests/Feature/PitchAtomicSaveTest.php` | 6.3 AC4, V4 |
| **HIGH** | Concurrent city-admin approval race | `tests/Feature/ConcurrentApprovalTest.php` | 6.1 AC4, V5 |
| **HIGH** | 14-day cap: >14d → 422; =14d → 200 | `tests/Feature/SpecialEventPitchTest.php` | 6.3 AC6, FR-SE-2 |
| **HIGH** | Upload: bad MIME and oversized file rejected on `/apply` | `tests/Feature/RoleApplicationUploadTest.php` | V6 |
| **MED** | Visible-not-bookable: SE pitch renders outside window, `canBook` false | `tests/Feature/SpecialEventPitchTest.php` | 6.3 AC7, FR-SE-3 |
| **MED** | Seeder idempotency: run twice → no duplicates, no errors | `tests/Feature/SeederIdempotencyTest.php` | T0.2 |
| **MED** | Nav: empty city renders empty state (not 404) | extend `LandingNavTest.php` | 6.5 AC2, FR-NAV-2 |
| **MED** | Search country-code normalization end-to-end | extend `EventsCalendarSearchTest.php` | FR-NAV-3 |
| **MED** | SE Admin many-per-city via approval | extend `RoleApplicationTest.php` | 6.2 AC1 |
| **LOW** | `type_id` restricted to city-admin subset on Space create | extend `SpaceHierarchyTest.php` | V3 |

---

## 12. Full automated test suite command reference

```bash
# Phase 0 — role/permission foundation
./vendor/bin/sail artisan test --filter=RolePermission
./vendor/bin/sail artisan test tests/Feature/CityAdministratorTest.php

# Phase 2 — Special Events Admin role
./vendor/bin/sail artisan test tests/Feature/RolePermission/SpecialEventsAdminPermissionTest.php

# Phase 3 — pitch FKs + scope
./vendor/bin/sail artisan test tests/Feature/PitchLocationFkTest.php

# Phase 5 — approval workflow
./vendor/bin/sail artisan test tests/Feature/RoleApplicationTest.php

# Phase 7 — nav, hierarchy, search, pins
./vendor/bin/sail artisan test tests/Feature/LandingNavTest.php
./vendor/bin/sail artisan test tests/Feature/SpaceHierarchyTest.php
./vendor/bin/sail artisan test tests/Feature/EventsCalendarSearchTest.php

# Cross-cutting — performer cancellation
./vendor/bin/sail artisan test --filter=PerformerCancellation

# Full suite (run this before and after every phase)
./vendor/bin/sail artisan test
```

---

## 13. Requirements traceability matrix

Complete lookup from requirement → phase → test coverage.

| Requirement | Description | Phase | Automated test | Manual verification |
|---|---|---|---|---|
| **FR-NAV-1** | Country→City→Pitch drill-down | 7 | LandingNavTest | Header nav + city drill-down |
| **FR-NAV-2** | No broken levels (404s / unique-ID errors) | 7 | LandingNavTest (partial) | Manual browse — T7.1 checks |
| **FR-NAV-3** | Working event search | 7 | EventsCalendarSearchTest | Search UI |
| **FR-NAV-4** | Color-coded map pins | 7 | EventsCalendarSearchTest (flag check) | Map UI |
| **FR-ACCT-1** | City Admin application form | 5 | RoleApplicationTest | `/apply` UI |
| **FR-ACCT-2** | SE Admin application form | 5 | RoleApplicationTest | `/apply?role=special_events_admin` UI |
| **FR-ACCT-3** | Approve → role + scope + verify + brand | 5 | RoleApplicationTest | Admin review UI + DB checks |
| **FR-ACCT-4** | One City Admin per city | 5 | RoleApplicationTest (replace case) | Concurrent edge case ⬜ gap |
| **FR-ACCT-5** | Many SE Admins per city | 2, 5 | SpecialEventsAdminPermissionTest | — |
| **FR-ACCT-6** | Manual creation by Super/Admin | 2 | SpecialEventsAdminPermissionTest | User create UI |
| **FR-SE-1** | SE Admin creates special pitches | 2, 3 | SpecialEventsAdminPermissionTest | Pitch create UI |
| **FR-SE-2** | 14-day max duration | 6 | ⬜ gap | Manual UI — T6.2 |
| **FR-SE-3** | Visible year-round / bookable in window | 6 | ⬜ gap | Manual UI — T6.2 |
| **FR-SE-4** | City + location linkage + distinct pin | 3, 7 | PitchLocationFkTest + EventsCalendarSearchTest | Map UI |
| **FR-SE-5** | SE Admin creates locations | 3 | PitchLocationFkTest (scope check) | Space create UI |
| **FR-CA-1** | Admin menu split Pitches / Bookings | 7 | — | Admin sidebar check |
| **FR-CA-2** | No type dropdown + hierarchy rules | 7 | SpaceHierarchyTest | Location form UI |
| **FR-CA-3** | Location `city_id` persisted | 3 | PitchLocationFkTest | DB check |
| **FR-CA-4** | Pitch scope + mandatory location | 3 | ⬜ gap | IDOR manual security test |
| **FR-PERF-1** | Performer books timeslot → event | — | Existing Booking tests | — |
| **FR-PERF-2** | Performer cancellation | Cross | PerformerCancellationTest | Cancel flow manual |
| **FR-PITCH-2** | Real city/location FKs on pitch | 3 | PitchLocationFkTest | DB check |
| **FR-PITCH-3** | All fields visible on create | 6 | — | Pitch create UI |
| **FR-PITCH-4** | City options scoped server-side | 3, 6 | ⬜ gap | Manual IDOR test |
| **FR-PITCH-5** | Rename + reorder fields | 6 | — | Pitch form UI |
| **FR-PITCH-6** | "Weekly days and hours" copy | 6 | — | Pitch form UI |
| **FR-PITCH-7** | Save with no events succeeds | 4 | — | Pitch create UI |
| **FR-PITCH-8** | Atomic save — no partial commit | 4 | — | Pitch save UI |
| **FR-PITCH-9** | Single timezone field | 6 | — | Pitch form UI |
| **FR-PITCH-10** | Gallery disabled | 6 | — | Pitch form UI |
| **FR-ADMIN-1** | Administrator globally scoped | 1 | CityAdministratorTest | Manual login check |
| **V1** | Pitch city IDOR | 3 | ⬜ gap | Manual IDOR test — §5.3 |
| **V2** | Unverified approval minting admins | 5 | RoleApplicationTest | `email_verified_at` DB check |
| **V3** | Mass-assignment IDOR | 3 | PitchLocationFkTest (scope) | Manual IDOR test — §5.3 |
| **V4** | Pitch save partial commit | 4 | — | Pitch save UI — §6.1 |
| **V5** | One-per-city + concurrent race | 5 | RoleApplicationTest (replace) | ⬜ concurrent test |
| **V6** | Public branding upload risk | 5 | ⬜ gap | Manual upload test — §7.2 |
| **V7** | Unregistered `SpaceEventPolicy` | 1 | CityAdministratorTest | Tinker policy check |
| **V8** | Hardcoded roles / orphaned seeder | 0 | RolePermission suite | DB/seed check — §2.2 |
| **V9** | Administrator without scope | 1 | CityAdministratorTest | Manual Administrator login |

---

## 10. Phase 8 — Events overhaul (FR-BOOK) — pitch timeslots → performer events

**Plan:** [`plan-events-overhaul-pitch-timeslots.md`](./plan-events-overhaul-pitch-timeslots.md)  
**Status:** T-TOOL-1/2/3 complete (RED target tests); T8.1–T8.5 TODO

### 10.1 Run target tests (expect FAIL until T8 lands)

```bash
./vendor/bin/sail artisan test --group=events-overhaul
```

| Test file | FR-BOOK | Unblocks when |
|---|---|---|
| `ProductEventRemovalTest.php` | FR-BOOK-5 | T8.1 |
| `PitchDirectBookingTest.php` | FR-BOOK-1/2/6 | T8.2 |
| `PitchAvailabilityListingTest.php` | FR-BOOK-3 | T8.3 |
| `PitchPublicEventCalendarTest.php` | FR-BOOK-4 | T8.4 |

### 10.2 Manual QA (post T8.5)

| Check | Steps | Expected |
|---|---|---|
| No admin Events tab | Edit pitch as City Admin | No ProductEvent list/modal |
| Performer list | Publish pitch with schedule only | Appears on `/booking/products` |
| Direct book | Book timeslot | Order + `events` row; no `product_events` row |
| Fully booked | Exhaust slots | Pitch still listed; book disabled |
| Public calendar | Guest opens pitch page | Upcoming booked events only |
| Batch book | Book 2 slots in one submit | Two orders/events |
| Cancel / reschedule | Existing flows | Still work (FR-BOOK-7) |

### 10.3 Blast-radius tooling

```bash
rg -l 'ProductEvent|product_events|product_event_id' --glob '!vendor/**' | sort > migration-preparation/artifacts/product-event-blast-radius.txt
```

Disposition checklist: `migration-preparation/artifacts/product-event-blast-radius-disposition.md`
