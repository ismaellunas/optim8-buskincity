# Refactor Progress Log

> Living record of execution against [`00-START-HERE-implementation-guide.md`](./00-START-HERE-implementation-guide.md).
> For a code-verified status report at any time, run the `refactor-progress-audit` skill (or ask "check refactor progress").
>
> **Status legend:** ✅ DONE (code + verification) · 🟢 CODE COMPLETE (verification pending) · 🟡 IN PROGRESS · ⛔ BLOCKED · ⬜ TODO

---

## 2026-06-05 — Phase 8 tooling (FR-BOOK target tests)

| Task | Status | Notes |
|---|---|---|
| **T-TOOL-1** Spec + FR-BOOK | 🟢 CODE COMPLETE | FRS §3.5b, plan doc, daniel doc |
| **T-TOOL-2** Blast-radius inventory | 🟢 CODE COMPLETE | 54 files; disposition md |
| **T-TOOL-3** RED target tests | 🟢 CODE COMPLETE | `@group events-overhaul` — 4 test files + `CreatesBookablePitch` trait |
| **T8.1–T8.5** Events overhaul implementation | ⬜ TODO | Next: T8.1 remove ProductEvent |

**Run:** `php artisan test --group=events-overhaul` (expect failures until T8.1+)

---

## Status board

| Task | Status | Notes |
|---|---|---|
| **T0.1** Canonical role registry | ✅ DONE | php -l clean; behavior-preserving. Live: all 5 roles resolve. `RolePermission` suite green (2026-06-02). |
| **T0.2** Idempotent consolidated seeder | ✅ DONE | Verified on RDS 2026-06-01: seeder ran (incl. via `safe-migrate` backup), all 5 roles present incl. `city_administrator` with its 4 perms (`system.dashboard, city.manage_events, city.view_reports, product.add`). |
| **T0.3** Unified role assignment service | ✅ DONE | php -l clean; behavior-preserving. `RolePermission` suite green (2026-06-02). |
| **T7.1** Slug resolution & broken nav levels | 🟢 CODE COMPLETE | php -l + lint clean; behavior-preserving fallbacks. Manual browse verification pending. |
| **T1.1** Generalized `user_scope` | 🟢 CODE COMPLETE | Table + backfill + dual-write helpers. One-per-city partial unique index added in Phase 5 (T5.2). |
| **T1.2** Register SpaceEventPolicy + Admin global | 🟢 CODE COMPLETE | Policy registered + Administrator/Super short-circuit (OQ13). OQ2=NO → creator-only ownership kept (no further change). |
| **T1.3** Remove dashboard for City Admin (OQ14) | 🟢 CODE COMPLETE | Seeder revokes `system.dashboard`; literals → config; redirect branches kept (now active). Needs re-seed + verify. |
| Phase 2 (T2.1/T2.2) | 🟢 CODE COMPLETE | SE admin role + scope UI; tests green after CityFactory fix. |
| **T3.1** locations + pitch FKs | 🟢 CODE COMPLETE | `locations` table + `lunar_products` FK columns + backfill; dual-read meta kept. |
| **T3.2** Server-side scope validation | 🟢 CODE COMPLETE | `UserScopeService`, scoped validation rules, controller/request wiring. |
| **T3.3** Space.city_id persistence | 🟢 CODE COMPLETE | `persistCityId()` + parent backfill migration. |
| **T4.1** Atomic pitch save | 🟢 CODE COMPLETE | See notes above. |
| **T5.1** role_applications + public apply form | ✅ DONE | `/apply?role=…`, uploads, reCAPTCHA + throttle. |
| **T5.2** Transactional approval + replace | ✅ DONE | Admin review at `/admin/role-applications`; replace modal; partial unique index; `RoleApplicationTest` (5 cases) green. |
| **T6.1** Unified pitch form + labels | 🟢 CODE COMPLETE | All fields on create; timeslot duration ↔ pitch date range swap; single timezone; gallery hidden. Pending: manual verify + tests. |
| **T6.2** 14-day SE cap + bookability | 🟢 CODE COMPLETE | `MaxInclusiveDaySpan`; advance booking within pitch window; `BookingWithinPitchWindow` on book API; scoped city picker on pitch form. |
| **T7.2** Data-driven country→city nav | ✅ DONE | `LandingNavService`; `MenuService::mergeLandingNavMenus()`; `getLeaves()` pitch filter; `LandingNavTest` (2 cases) green. |
| **T7.3** Remove type dropdown + hierarchy rules | ✅ DONE | `SpaceHierarchyService`; type inferred from parent/role; `SpaceForm.vue` dropdown removed; `SpaceHierarchyTest` (4 cases) green. |
| **T7.4** Map pins + event search | ✅ DONE | View adds `is_special_event` + alpha2 country; `space_event` included; 30-day default window; color-coded pins; `EventsCalendarSearchTest` (4 cases) green. |
| **T-PERF-CANCEL** Performer cancellation | ✅ DONE | `cancelBooking()` transactional flow; `blockingAvailability` scope frees slots; frontend cancel route; `PerformerCancellationTest` green. |

**Verification still required to mark Phase 0 ✅ DONE** (needs a configured DB; not run here to avoid touching local data):
1. `php artisan migrate:fresh --seed` → assert all 5 roles exist incl. `city_administrator`, and the role→permission map matches the pre-refactor state.
2. Re-run `php artisan db:seed` → assert no errors / no duplicates (idempotency).
3. `php artisan test` (esp. `tests/Feature/RolePermission/*`, user/role CRUD, `CityAdministratorTest`) → green.

---

**Verify:** browse header — each country dropdown lists its cities; city page lists pitch leaves; empty city shows no pitch cards (not 404).

---

## 2026-06-04 — T-PERF-CANCEL executed (performer cancellation)
| Task | Status | Notes |
|---|---|---|
| **T-PERF-CANCEL** Performer cancellation | ✅ DONE | OQ4: cancel marks order/event `canceled`; slot freed via `blockingAvailability` scope; `OrderService::cancelBooking()`; frontend cancel wired to `FrontendOrderController`. |

---

## 2026-06-04 — Phase 7 T7.4 executed (map pins + event search)
| Task | Status | Notes |
|---|---|---|
| **T7.4** Map pins + event search | ✅ DONE | `event_calendars` view: alpha2 country + `is_special_event`; `availableTypes()` includes `space_event`; 30-day default window; color-coded Google Maps markers; `EventsCalendarSearchTest` (4 cases) green. |

---

## 2026-06-04 — Phase 7 T7.2 executed (data-driven landing nav)
| Task | Status | Notes |
|---|---|---|
| **T7.2** Country→City header nav | ✅ DONE | `LandingNavService` builds menus from Space tree; replaces CMS "Country" stub in `MenuService`; pitch leaves filtered in `PageSpaceService::getLeaves()`. |

---

## 2026-06-04 — Phase 5 verified (RoleApplicationTest green)
| Task | Status | Notes |
|---|---|---|
| **T5 tests** | ✅ DONE | `RoleApplicationTest` (5 cases) green after: seed `PermissionSeeder` before `RolesAndPermissionsSeeder`; `User::factory()` on approve; `$confirmReplace` in transaction closure. |
| **T5.1/T5.2** | ✅ DONE | Phase 5 code + automated verification complete. Staging smoke on `/apply` → approve/reject/replace still recommended. |

---

## 2026-06-04 — Phase 6 executed (pitch UX + 14-day rule)
| Task | Status | Notes |
|---|---|---|
| **T6.1** Unified pitch form | 🟢 CODE COMPLETE | `ProductForm.vue` field order/labels; gallery disabled; `ProductCreate.vue` full form + transactional `store()` via `ProductPitchRequest`. |
| **T6.2** 14-day cap | 🟢 CODE COMPLETE | `MaxInclusiveDaySpan`; SE admin pitches get `is_special_event`; frontend `canBook` gated by `isWithinBookableWindow()`. |

**Verify:** create pitch as city admin (no 14d cap) + as SE admin (≤14d); confirm single timezone saves; booking UI hidden outside SE window.

---

## 2026-06-04 — Phase 5 executed (application → approval workflow)
| Task | Status | Notes |
|---|---|---|
| **T5.1** role_applications + branding | 🟢 CODE COMPLETE | `role_applications` table; public `/apply` form; logo/cover upload validation. |
| **T5.2** Approval + replace | 🟢 CODE COMPLETE | Transactional approve/reject; OQ3 replace confirmation; `user_scope_one_city_admin_per_city` index; branded city Space provisioning. |
| **T5 tests** | ✅ DONE | `RoleApplicationTest` (5 cases) green. |

**Apply on RDS:** `./scripts/db-etl.sh safe-migrate` (migrations: `role_applications`, one-per-city index).

**If unique index fails** (duplicate city admins per city): dry-run then execute dedupe before re-migrating:
`sail artisan user-scope:dedupe-city-admins` → `sail artisan user-scope:dedupe-city-admins --execute`

**URLs:** Public `/apply?role=city_administrator` or `special_events_admin`; Admin `/admin/role-applications` (Administrator/Super Admin).

---

## 2026-06-02 — Client decisions batch 2
- **OQ1 NO** — SE Admin cannot edit normal pitches. · **OQ2 NO** — creator-only ownership (no city-wide cross-admin editing → T1.2 city scope = creator-based). · **OQ3 REPLACE** with an info modal before replacing (enables one-per-city index + replace flow). · **OQ4 YES** — performer can cancel; cancel removes event + frees slot. · **OQ5 NO if same location** — forbid same-location overlap; allow different locations. · **OQ6 CORRECT** (hierarchy). · **OQ7 YES** (landing nav). · **OQ8 NEW TABLE** (`locations`). · **OQ11** new feature — spec pitch logo+cover.
- **OQ12 ANSWERED:** performers book timeslots (existing `Performer` role); no `street_performer` role needed.
- **OQ9 ANSWERED (Option A):** Relational `App\Models\City`/`Country` canonical; new `locations` FKs → `cities`; Space City/Country nodes become presentation-only via `spaces.city_id`; normalize country code to alpha3.
- **✅ ALL open questions (OQ1–OQ14) are now answered. No remaining blockers.**
- **OQ11 spec written:** `req-T-PITCH-BRANDING-logo-cover.md` — pitch logo + cover via existing `mediables.type` pivot (`logo`/`cover`/`gallery`), no new table; controller/form/validation/public-render changes detailed; fallback chain keeps existing pitches unchanged. **Sub-questions resolved:** SQ-A no-cap · SQ-B both required to publish (draft may be empty; existing published pitches grandfathered) · SQ-C hard-enforce dims (logo ~1:1 ≥256², cover ~8:3 ≥1200×450) · SQ-D delete underlying Media on removal unless shared. Spec is now **FINAL**.
- Net effect: Phase 2 (SE admin), Phase 5 (approval/replace), Phase 6 (14-day + overlap) are unblocked. Phase 3/7 data model still need **OQ9**.

## 2026-06-02 — Phase 3 executed (pitch FKs + locations + scope validation)
| Task | Status | Notes |
|---|---|---|
| **T3.1** locations + pitch FK columns | 🟢 CODE COMPLETE | `locations` table; `lunar_products.city_id/location_id/is_special_event`; backfill from meta + productable Spaces. |
| **T3.2** Server-side scope validation | 🟢 CODE COMPLETE | `UserScopeService`, `InScopedCityId(s)` rules; wired into ProductEvent, Space, User city sync paths. |
| **T3.3** Space.city_id persistence | 🟢 CODE COMPLETE | `SpaceService::persistCityId()` + parent backfill migration. |
| **T3 tests** | 🟡 WRITTEN, UNRUN | `PitchLocationFkTest` (4 cases). Run after `safe-migrate`. |

**Apply on RDS:** `./scripts/db-etl.sh safe-migrate` (3 new migrations: locations, product FKs, spaces city_id backfill).

## 2026-06-02 — Phase 2 executed (Special Events Admin role)
| Task | Status | Notes |
|---|---|---|
| **T2.1** Define & seed `special_events_admin` | 🟢 CODE COMPLETE | `UserRole` enum + `config/permission.php` role_names + `RolesAndPermissionsSeeder` (perms: `special_events.manage`, `product.add`, `public_page.profile`; NO dashboard per OQ14). |
| **T2.2** Assignable + city scope UI | 🟢 CODE COMPLETE | Role auto-appears in dropdowns; `User` model scope helpers generalized; `CityUserController`/`UserController` role-aware (SE admin → user_scope only); `User/Edit.vue` panel shown for SE admin too. |
| **T2 tests** | 🟡 WRITTEN, UNRUN | `SpecialEventsAdminPermissionTest` (7 cases). Docker was down → run `sail artisan test --filter=SpecialEventsAdminPermissionTest`. |

**Files touched:** `app/Enums/UserRole.php`, `config/permission.php`, `database/seeders/RolesAndPermissionsSeeder.php`, `app/Models/User.php`, `app/Http/Controllers/Api/CityUserController.php`, `app/Http/Controllers/UserController.php`, `resources/js/Pages/User/Edit.vue`, `tests/Feature/RolePermission/SpecialEventsAdminPermissionTest.php`.
**Assumptions:** SE-admin permission set is minimal (`special_events.manage` is a forward hook refined in Phase 6); SE admins are user_scope-only (no `city_user`). All `php -l` + lint clean.
**Pending verify:** re-seed on target DB; run the feature test once Docker is up.

## 2026-06-02 — Phase 1 executed (T1.1, T1.2 partial, T1.3)
**T1.1 — Generalized scope (`user_scope`)** — additive.
- New `user_scope (id, user_id, role, scope_type, scope_id, timestamps)` migration + `App\Models\UserScope`. Indexes on `(user_id, role)` and `(scope_type, scope_id)`; composite unique `(user_id, role, scope_type, scope_id)`.
- **Backfill** from `city_user` (idempotent, chunked) → role=`city_administrator`, scope_type=`city`. `city_user` KEPT (dual-read/dual-write; not dropped).
- `User`: added `userScopes()`, `scopeIdsFor()`, `inScope()`, and `syncAdminCities()` (writes BOTH `city_user` and `user_scope`). Routed `UserController@store` + `Api\CityUserController@update` through `syncAdminCities()`.
- ⚠️ **Deferred:** the one-city-admin-per-city partial unique index — conflict behavior is OQ3 (unanswered). Will add in the approval phase once decided.

**T1.2 — Policy enforcement (partial).**
- Registered `SpaceEvent::class => SpaceEventPolicy::class` in `AuthServiceProvider` (was unregistered — V7/V9).
- Added `SpaceEventPolicy::before()` → Administrator/Super allowed globally (OQ13).
- ⚠️ **Not changed (OQ2-blocked):** `SpacePolicy::manage` / `ProductPolicy::canManageProductSpace` still key on creator/ancestor ownership, not city-wide. City-wide cross-admin editing awaits OQ2.

**T1.3 — Dashboard access (OQ14).**
- Removed `system.dashboard` from the `city_administrator` map and added an explicit, idempotent **revoke** in `RolesAndPermissionsSeeder` (handles existing grants from the old orphaned seeder).
- Swapped `'city_administrator'` literals → `config('permission.role_names.city_admin')` in `MenuService` and `LoginResponse`.
- The `&& !can('system.dashboard')` branches are **kept**: previously inert (City Admins *had* dashboard), now correctly route City Admins to Spaces.

**To apply on RDS (user-run):**
1. `./scripts/db-etl.sh safe-migrate` — backup + run the `user_scope` migration (additive).
2. `sail artisan db:seed --class=RolesAndPermissionsSeeder` — applies the dashboard revoke (run twice to confirm idempotent).
3. Verify: City Admin login lands on Spaces (not dashboard); `sail artisan test --filter=RolePermission` + `CityAdministratorTest` green.

## 2026-06-02 — Client decisions (unblocks Phase 1)
- **OQ10:** Generalize `city_user` → **`user_scope (user_id, role, scope_type, scope_id)`**.
- **OQ13:** **Yes** — `Administrator` is globally scoped, may act across all cities and **approve** applications. *(Interpreted as yes to both parts of the question; flag if narrower.)*
- **OQ14:** **No** — City/Special-Events Admins must **not** have full `system.dashboard` access. → Phase 1 will remove `system.dashboard` from `city_administrator` and not grant it to the SE admin (behavior change), and remove the dead-code `!can('system.dashboard')` branches.
- Still open (needed for Phase 3+): OQ6, OQ8, OQ9 (data model), and behavioral OQ1/2/3/4/5/7/11/12.

## 2026-06-02 — T7.1 slug resolution / broken nav levels (FR-NAV-2)
Three frontend defects fixed (code complete; manual verification pending):
1. **`PageSpaceService::getPageTranslationFromRequest`** — resolved multi-segment URLs by the **last segment only** (`whereSlug($slugs->last())`), causing wrong-page matches on colliding leaf slugs. Now disambiguates by the **full ancestor path** (`getSlugs() === path`), falling back to the prior first-match for zero-regression on single candidates. Also filters empty segments.
2. **`PageTranslation::getSlugs`** — fell back to `$t->uniqueKey` (no such attribute → null) when slug was null; fixed to `$t->unique_key`, made null-safe, and now `filter()`s out null/empty segments so URLs never contain `//` (FR-NAV-2 "never emit null segments").
3. **`Frontend\SpaceController::redirectOrShowPage`** — self-redirect passed a **positional single slug** to a route expecting the named `slugs` param with the full path → 404s/loops on nested pages. Now redirects with `['slugs' => $localizedTranslation->getSlugs()]`.

**Verify (manual):** browse a populated level (country→city→pitch) and an **empty** level (city/pitch with no children) — both should render (empty state), no 404 / unique-ID error. Note: deeper data-integrity fix (admin SpaceController creating `slug=null, unique_key=null`) is separate and tracked under Phase 7 / not in scope of T7.1.

## 2026-06-02 — Phase 0 fully verified; test harness fixed
- **Phase 0 complete (T0.1, T0.2, T0.3 all ✅).** `sail artisan test --filter=RolePermission` passes.
- Pre-existing test-config bug fixed (unrelated to refactor): `pgsql_testing` inherited `DB_HOST` from `.env` (RDS), so tests hit RDS looking for a `testing` DB. Added `<server name="DB_HOST" value="pgsql"/>` to `phpunit.xml` so tests use the local Sail container. Then reset the local container (`sail down -v && sail up -d`) so it re-initialized with current `.env` creds + auto-created the `testing` DB. RDS untouched.

## 2026-06-01 (later) — Phase 0 verified on RDS
- `safe-migrate` run (backup taken; `migrate` = nothing to migrate, expected — Phase 0 has no migrations).
- `db:seed --class=RolesAndPermissionsSeeder` run; tinker check confirms 5 roles incl. `city_administrator` (previously orphaned) with the expected 4 permissions. T0.2 → ✅ DONE.
- Still recommended (non-blocking): `sail artisan test --filter=RolePermission` to lock in T0.1/T0.3.

## 2026-06-01 — Phase 0 executed (T0.1, T0.2, T0.3)

### T0.1 — Canonical role registry
- **Added** `app/Enums/UserRole.php`: backed enum with the 5 canonical role-name strings exactly as stored in the DB (`Super Administrator`, `Administrator`, `Author`, `Performer`, `city_administrator`) + `values()` helper. Values intentionally preserve the mixed Title-Case / snake_case convention (no row rename — guardrail §4).
- **Edited** `config/permission.php`: extended `role_names` with `author` and `city_admin => 'city_administrator'` (additive only).
- **Swapped read-only literals** (all resolve to identical values → zero behavior change):
  - `app/Providers/AuthServiceProvider.php` — `Gate::after` now uses `config('permission.super_admin_role')`.
  - `app/Http/Controllers/RegisteredUserController.php` — `User::role(config('permission.super_admin_role'))`.
  - `app/Models/User.php` — `isCityAdmin()` / `isCityAdministrator()` now use `config('permission.role_names.city_admin')`.

### T0.2 — Idempotent consolidated seeder
- **Added** `database/seeders/RolesAndPermissionsSeeder.php`: single declarative role→permission map (Administrator = core wildcards + `system.*`; Author = `system.dashboard`; Performer = `payment.management`, `public_page.profile`; city_administrator = `system.dashboard`, `city.manage_events`, `city.view_reports`, `product.add`). Uses `firstOrCreate` for roles/permissions and (idempotent) `givePermissionTo`; never removes grants; clears the Spatie cache at the end. Super Administrator intentionally gets no explicit grants (bypasses via `Gate::after`).
- **Registered** it in `database/seeders/DatabaseSeeder.php` immediately after `UserAndPermissionSeeder`. On fresh seed it reconciles idempotently and **fixes the orphaned `CityAdministratorSeeder`** (city_administrator role now seeded on fresh installs).
- **Hardened module seeders** to be re-runnable: `Permission::create` → `Permission::firstOrCreate` in `modules/{Space,Ecommerce,FormBuilder}/Database/Seeders/PermissionSeeder.php`.

### T0.3 — Unified role assignment service
- **Added** `app/Services/UserRoleService.php` with `syncSingleRole(User, string|int|null $role, bool $forgetCache = true)` encapsulating detach-then-assign (accepts role name or id).
- **Migrated call sites:**
  - `app/Http/Controllers/UserController@store` and `@update` → use `UserRoleService` (import added).
  - `modules/FormBuilder/Services/AutomateUserCreationService::assignRole` → delegates to `UserRoleService` with `forgetCache: false` to match prior behavior (import added).

---

## Assumptions made during Phase 0 (confirm if disputed)

- **Registry scope = core-only.** Module-owned permissions (`product.*`, `order.*`, Space/FormBuilder wildcards) remain seeded/granted by their module seeders; the consolidated core seeder only expands wildcards that exist when it runs. This matches the prior behavior of `UserAndPermissionSeeder` and keeps blast radius minimal. (Open question `registry_scope` was not answered; chose the lower-risk default rather than assume centralization.)
- **`UserController@store` null-role edge:** previously `assignRole($request->role)` could be called with a null role when the field was present-but-empty (latent error). The unified service now safely detaches in that case. This is strictly safer, not a regression.
- **Old seeders left in place** (`RoleSeeder`, `UserAndPermissionSeeder`, `CityAdministratorSeeder`) for backward compatibility; the new seeder is authoritative and idempotent. Fully removing the duplication is a follow-up once DB-verified.

## Guardrails honored
- No DB role rows renamed; only references centralized.
- All seeder changes additive/idempotent; no drops.
- No dual-portal, Spatie-internal, or Lunar internals touched.
- No blocked (OQ-gated) task started.
