# 00 — START HERE: AI Implementation Guide & Task List

> Single entry point for an AI agent (or developer) executing the BuskinCity refactor.
> Generated: 2026-06-01 · Status: **EXECUTION STARTED — Phase 0 in progress.**
> This file is an INDEX + TASK LIST. It does not duplicate the analysis; it points to it and tells you the order, the guardrails, the blockers, and the exact references for each task.
>
> **Live progress is tracked in [`PROGRESS-LOG.md`](./PROGRESS-LOG.md).** Run the `refactor-progress-audit` skill (or ask "check refactor progress") for a code-verified status report. Checkboxes below reflect code-complete state; the log records verification status and assumptions.

---

## 0. How to use this document

1. **Read this whole file first.** It is the map. Do not start editing code from any other doc without reading §1–§6 here.
2. **Before touching code for a task**, open the referenced sections in the linked docs (column "References" in §7) and read the cited source files.
3. **Respect the guardrails in §4** and the **blockers in §6** (some tasks are gated on client answers — do not silently assume; if blocked, stop and ask).
4. **Work top-down through the phases in §7.** Phases are dependency-ordered. Do not jump ahead (e.g., do not fix pitch save before the FK promotion, do not build approval before the role foundation).
5. **One behavior change per PR.** Each task = roughly one PR. Update the checkbox and run the task's verification before moving on.
6. **This is documentation work's hand-off to implementation. Until a client answer unblocks a blocked task, produce code only for unblocked tasks.**

---

## 1. Mission (what we are building)

Turn BuskinCity from a loosely-coupled CMS + booking engine into a **structured, geographically-scoped marketplace** with:
- A public drill-down: **Country → City → Pitch → Events**.
- Two **self-service, approval-gated, city-scoped admin roles**: **City Admin** (one per city) and a new **Special Events Admin** (many per city, 14-day bookable special events).
- Reliable pitch creation, server-side scope enforcement, working navigation/search, and color-coded map pins.

The work sits on top of an already-specified **role/approval refactor** (the `user-approval-roles-*` docs). Do that foundation first.

---

## 2. Domain glossary — REQUIREMENT WORD → ACTUAL CODE (read this; it prevents the biggest mistakes)

The client's vocabulary does **not** map 1:1 to the code. Internalize this before any task.

| Client term | Actual code reality | Consequence |
|---|---|---|
| **Pitch** | Lunar `Product` (`product_type='Event'`); location is a **free-text JSON meta string** (`locations`), **no `city_id` FK**; schedule via morphed `Schedule`. | "Limit cities to those created by X" is **not enforceable** until location becomes real FKs. |
| **Timeslot** | **Not a row.** Computed from `Schedule → ScheduleRule → ScheduleRuleTime` minus booked `events`. | 14-day rule lives in validation + availability math, not a column. |
| **Event** (booked timeslot) | Row in **`events`** (+ `orders`, `order_lines` type EVENT) via `OrderService::bookEvent()`. | "Booked timeslot becomes an event" already matches reality. |
| **"Event" (admin Events tab)** | `ProductEvent` (`product_events`) = admin-defined bookable window/campaign. | Word "event" is **overloaded** (`ProductEvent` vs booked `Event`). Keep distinct. |
| **Location** | A `Space` (`spaces`, nested set); Space UI relabels "Space"→"Location". Has a **`type_id`**. | "Location has no type" = redesign the Space `type_id` mechanism. |
| **Location "Type"** (None/Country/Pitch/City/Special Events) | `global_options` lookup (`type='space'`) → `spaces.type_id`. | Type is the **discriminator** for Country/City/Pitch nodes — cannot just be deleted. |
| **City** | TWO parallel models: `App\Models\City` (`cities` + `city_user` scope) **and** Space-type "City" node. | Must reconcile (Blocker OQ6/OQ9). |
| **Country** | TWO parallel models: `App\Models\Country` (`countries`, alpha2/alpha3) **and** Space-type "Country" node. | Landing nav uses Space tree; scope uses `country_code`. Country-code length mismatch (2 vs 3) breaks search. |

---

## 3. Reference library (what each doc is for, and when to read it)

All paths are under `migration-preparation/` unless noted.

| Doc | Read it when you need… |
|---|---|
| **`00-START-HERE-implementation-guide.md`** (this file) | The plan, order, guardrails, blockers, task list. **Always first.** |
| `new-requirements-frs-and-refactor-plan.md` | The **FRS** (FR-NAV/ACCT/SE/CA/PERF/PITCH/ADMIN), **current-state mapping** (req → real files), **role matrix**, **ERM**, **user stories w/ acceptance criteria**, **open questions**. Primary spec for *what* to build. |
| `new-requirements-security-scalability-and-phasing.md` | **Vulnerabilities V1–V9** (with file:line evidence + mitigations), **scalability** analysis, and the **per-phase plan** (goal/changes/risk/revertibility/tests). Primary spec for *how/safely*. |
| `user-approval-roles-rbac-map.md` | The current RBAC reality: roles, permissions, where they're defined/checked/enforced, DB tables, **risks R1–R11**. Foundation context for Phase 0–2. |
| `user-approval-roles-refactor-plan.md` | The **safe phased role/approval refactor** (PR 1–6) that Phase 0–1 here execute. Read before Phase 0. |
| `booking-module-execution-flow.md` | Pitch/Product/Event/ProductEvent/booking internals. Read for Phases 3, 4, 6. |
| `spaces-module-execution-flow.md` | Space/Location/City-node, nested set, slug resolution. Read for Phases 1, 7. |
| `form-builder-execution-flow.md` | The "Automate User Creation" path reused/replaced by approval. Read for Phase 5. |
| `auth-execution-flow.md` | Login, verification, dual-portal session, middleware chain. Context for Phases 0, 5. |
| `user-management-execution-flow.md` | Admin user CRUD, role assignment, city sync, profile. Context for Phases 0, 2, 5. |
| `feature-dependency-map.md`, `entry-points-map.md` | Cross-feature dependencies & every route/entry point. Use to find blast radius before edits. |
| `frontend-execution-flow.md` | Inertia/Vue page flow, menus. Context for Phases 6, 7. |
| `target-architecture-proposal.md` | Prior architectural direction (cross-check). |
| `docs/roles/ROLE_CREATION_AND_RBAC_GUIDE.md`, `docs/roles/NEW_ROLE_IMPLEMENTATION_CHECKLIST.md`, `docs/roles/CityAdministratorRole.md` (repo `docs/`) | Existing role-creation playbook. **Note:** partly stale (uses `'City Administrator'` display name while code uses `city_administrator`). Reconcile per Phase 0. |

> Other execution-flow docs (admin-cms, dashboard-widgets, donations, payments-stripe, system-settings, theme-appearance, legacy-forms, logging-monitoring) are background; consult only if a task touches those areas.

---

## 4. Guardrails (HARD RULES — do not violate)

**Never touch yet:**
- Dual-portal session logic: `home_url`, `EnsureLoginFrom*` middleware, `AuthenticateLoginAttempt`.
- Existing role **names** in the DB (`Super Administrator`, `Administrator`, `Author`, `Performer`, `city_administrator`). Centralize *references*, never rename rows.
- `enable_wildcard_permission` and Spatie internals.
- FormBuilder mapping schema (reuse; don't restructure).
- Lunar/Ecommerce internals beyond the additive pitch FK columns.

**Migration safety:**
- All migrations **additive and forward-only**. No column drop/rename in the same release that introduces a replacement.
- **Do not drop** `city_user`, `spaces.type_id`, or product meta `locations` until every reader is migrated (dual-read during transition).
- Backfill data migrations must be **idempotent and chunked**; never lock existing users out.

**Process:**
- Backward-compatible first; additive then subtractive.
- Idempotent seeders (`firstOrCreate` / `syncPermissions`); register the consolidated seeder in `DatabaseSeeder`.
- One behavior change per PR; each PR independently revertible.
- Clear the Spatie permission cache after role/permission changes.
- **Do not silently assume** answers to blockers (§6). If a task is blocked, stop and ask.

---

## 5. Definition of done (applies to every task)

A task is done when: code change is scoped to the task; the task's **verification/tests** (§7) pass; existing `tests/Feature/RolePermission/*` and relevant module tests stay green; no guardrail (§4) violated; the checkbox here is ticked and the PR notes which FR/V IDs it satisfies.

---

## 6. BLOCKERS — open questions that gate work (get client answers; do not assume)

Tasks reference these by ID. A task marked "Blocked by OQx" must not ship its decision-bearing parts until OQx is answered. Where planning needed a position, it is an **ASSUMPTION (needs confirmation)** — confirm before relying on it.

| OQ | Question | Gates |
|---|---|---|
| OQ1 | Can a Special Events Admin edit **normal** pitches? | T-SE, role matrix — ✅ **ANSWERED (2026-06-02): NO.** SE Admin may only manage special-events pitches, not normal pitches. |
| OQ2 | Can a City Admin edit pitches created by **another** City Admin (ownership change)? | T-CA-SCOPE — ✅ **ANSWERED (2026-06-02): NO.** Creator-only ownership; a City Admin cannot edit another admin's pitches. (Keeps current `SpacePolicy::manage` creator/ancestor model — unblocks T1.2 city scope: scope by creator, not city-wide.) |
| OQ3 | City-admin application for a city that **already has one** — reject / transfer / replace? | T-APPROVAL (one-per-city) — ✅ **ANSWERED (2026-06-02): REPLACE**, but show an **information modal before replacing** (warn the approver that the existing City Admin will be replaced). → enables the deferred one-per-city unique index + a replace flow in approval. |
| OQ4 | Can a performer **cancel** a booking? Does the event disappear or free the slot? | T-PERF-CANCEL — ✅ **ANSWERED (2026-06-02): YES** — performer may cancel; on cancel the event is removed AND the timeslot returns to available ("yes to both"). |
| OQ5 | Can Special Events pitches **overlap** normal pitches? | T-SE, availability/map — ✅ **ANSWERED (2026-06-02): NO if at the SAME location** (overlap forbidden when location matches; allowed at different locations). → availability/validation must reject same-location time overlaps. |
| OQ6 | Confirm canonical hierarchy Country→City→Location→Pitch→Timeslot→Event. | **Phase 3, 7 (central)** — ✅ **ANSWERED (2026-06-02): CORRECT** (confirmed). |
| OQ7 | Confirm landing nav Country→City→Pitch→Events + fixed menu labels. | T-NAV — ✅ **ANSWERED (2026-06-02): YES** (confirmed). |
| OQ8 | "Location" model: new `locations` table OR `Space` with `type` hidden/locked? | **Phase 7, T-LOC** — ✅ **ANSWERED (2026-06-02): NEW TABLE** (`locations`). Don't repurpose Space `type_id`. |
| OQ9 | Reconcile dual City/Country models — which is canonical? | **Phase 3, 7** — ✅ **ANSWERED (2026-06-02): Option A — Relational (`App\Models\City`/`Country`) is canonical.** New `locations` table FKs → `cities`; Space 'City'/'Country' nodes become presentation-only, linked via existing `spaces.city_id → cities.id`; normalize country-code length (alpha3). |
| OQ10 | Scope model: generalize `city_user`→`user_scope` OR a second SE-admin pivot? | **Phase 1** — ✅ **ANSWERED (2026-06-02): generalize to `user_scope (user_id, role, scope_type, scope_id)`.** |
| OQ11 | Pitch "logos and cover" — pitches have none today; does this mean city-page/Space branding? | T-PITCH-UX — ✅ **ANSWERED (2026-06-02): It's a NEW feature.** Requirement spec **FINAL** → **`req-T-PITCH-BRANDING-logo-cover.md`** (reuses existing `mediables.type` pivot — no new table). SQ-A=no-cap · SQ-B=required-to-publish · SQ-C=hard-enforce dims · SQ-D=delete-asset-if-unused. |
| OQ12 | "Only street performers can create events?" — admin ProductEvent windows or performer bookings? (no `street_performer` role exists) | T-SE, T-PERF — ✅ **ANSWERED (2026-06-02): performers BOOK timeslots (creating a booked Event); "street performer" = the existing `Performer` role. NO new role needed.** |
| OQ13 | Should `Administrator` be globally scoped, and may Administrators **approve** applications? | T-ADMIN-SCOPE, T-APPROVAL — ✅ **ANSWERED (2026-06-02): YES — Administrator is globally scoped, acts across all cities, and may approve applications.** |
| OQ14 | Do City/SE Admins need full `system.dashboard`? (today's seeder grants it → dead-code branches) | Phase 1, 2 — ✅ **ANSWERED (2026-06-02): NO — City/Special-Events Admins must NOT have full dashboard access. Remove `system.dashboard` from `city_administrator`; do not grant to SE admin.** |

> **Recommended to answer before Phase 3:** OQ6, OQ8, OQ9, OQ10 (they define the data model). Phases 0–2 can proceed while these are pending.

---

## 7. THE TASK LIST (dependency-ordered)

Each task: **ID · title · goal · depends-on · key files to edit · references · verification · blockers.**
References use: FRS doc = `new-requirements-frs-and-refactor-plan.md`; SEC doc = `new-requirements-security-scalability-and-phasing.md`; ROLE doc = `user-approval-roles-refactor-plan.md`; RBAC doc = `user-approval-roles-rbac-map.md`.

### PHASE 0 — Role/permission foundation (must land first; zero behavior change)
*Equivalent to ROLE doc PR 1–3. Fixes V8/R2/R4/R8.*

- [x] **T0.1 — Canonical role registry.** Goal: one source of truth for role names. — **✅ DONE 2026-06-02** (behavior-preserving; `RolePermission` suite green).
  - Files: `config/permission.php` (extend `role_names`, add `city_administrator`), new `app/Enums/UserRole.php` (or constants).
  - Swap read-only literals: `app/Providers/AuthServiceProvider.php` (`Gate::after` `'Super Administrator'`), `app/Http/Controllers/RegisteredUserController.php`, `app/Models/User.php` (`isCityAdmin*`).
  - References: ROLE doc PR 1; RBAC doc §5.2, R4/R8; SEC doc V8.
  - Verify: registry resolvable unit test; full suite green; no behavior change.
- [x] **T0.2 — Idempotent consolidated seeder.** Goal: one declarative `[role => permissions[]]` map; fix orphaned `CityAdministratorSeeder`. — **✅ DONE** (verified on RDS: all 5 roles incl. `city_administrator` + perms; idempotent).
  - Files: new `database/seeders/RolesAndPermissionsSeeder.php`; register in `database/seeders/DatabaseSeeder.php`; reconcile `RoleSeeder`, `UserAndPermissionSeeder`, `CityAdministratorSeeder`, and module `PermissionSeeder`s (Space/Ecommerce/FormBuilder).
  - References: ROLE doc PR 2; SEC doc §8.1, V8; RBAC doc §3.4.
  - Verify: `migrate:fresh --seed` parity (incl. `city_administrator`); seeder idempotent on re-run; existing users keep roles.
- [x] **T0.3 — Unified role assignment service.** Goal: kill duplicated detach+assign logic. — **✅ DONE 2026-06-02** (behavior-preserving; `RolePermission` suite green).
  - Files: new `app/Services/UserRoleService.php`; migrate call sites `app/Http/Controllers/UserController.php` (store/update) and `modules/FormBuilder/Services/AutomateUserCreationService.php::assignRole` (byte-equivalent behavior).
  - References: ROLE doc PR 3; RBAC doc R7.
  - Verify: assign/reassign/detach unit tests; existing user/role tests green.

### PHASE 1 — Generalize scope + formalize City Admin (fixes V7, V9, V8 dead-code)
- [x] **T1.1 — Generalized scope model.** Goal: role-aware scope (not City-Admin-only). — **🟢 CODE COMPLETE 2026-06-02** (php -l + lint clean). `user_scope` table + backfill + `User::scopeIdsFor/inScope/syncAdminCities` (dual-write). ⚠️ one-city-admin-per-city **partial unique index DEFERRED** pending OQ3. _Run: `db-etl.sh safe-migrate` then re-seed. Verify pending._
  - Files: new migration `user_scope` `(user_id, role, scope_type, scope_id)` + partial unique index for one-city-admin-per-city; forward-migrate `city_user` (dual-read, do NOT drop); `app/Models/User.php` helpers (`scopesFor`, `inScope`).
  - References: SEC doc §8.2, V5; FRS doc §5.2–5.3; **Blocked by OQ10** (generalize vs second pivot).
  - Verify: scope helpers unit-tested; `city_user` still readable.
- [x] **T1.2 — Register & route `SpaceEventPolicy`; fix scoped policies for Administrator.** — **🟢 CODE COMPLETE 2026-06-02**: registered `SpaceEventPolicy` + Administrator/Super global short-circuit (OQ13). **OQ2 = NO (creator-only)** → the existing `SpacePolicy::manage`/`ProductPolicy::canManageProductSpace` creator-ownership model is the intended behavior; no further change needed.
  - Files: `app/Providers/AuthServiceProvider.php` (register `SpaceEventPolicy`); `app/Policies/SpaceEventPolicy.php`; `modules/Ecommerce/Policies/ProductPolicy.php` (`canManageProductSpace`), `modules/Space/Policies/SpacePolicy.php` — short-circuit for Administrator/Super (FR-ADMIN-1); `modules/Space/Http/Middleware/CanManageEvent.php`.
  - References: SEC doc V7, V9; FRS doc §3.7 FR-ADMIN-1, §4 role matrix footnotes; **Blocked by OQ13** (Admin global scope).
  - Verify: cross-city denial tests; Administrator can act on all cities; `CityAdministratorTest` green.
- [x] **T1.3 — Resolve `system.dashboard` intent (OQ14).** — **🟢 CODE COMPLETE 2026-06-02**: removed `system.dashboard` from `city_administrator` map + active revoke in seeder; swapped role literals to config in `MenuService`/`LoginResponse`. NOTE: the `&& !can('system.dashboard')` branches are **kept** — with the permission removed they now correctly route City Admins to Spaces (they were inert before, not dead). _Run: re-seed. Verify: city-admin login lands on Spaces, no dashboard._
  - Files: `app/Services/MenuService.php`, `app/Http/Responses/LoginResponse.php` (city-admin `&& !$user->can('system.dashboard')` branches).
  - References: SEC doc V8; FRS doc OQ14; **Blocked by OQ14.**
  - Verify: city-admin login/menu behavior matches the confirmed intent.

### PHASE 2 — Add Special Events Admin role (seeded, unassigned first)
- [x] **T2.1 — Define & seed `special_events_admin`.** — **🟢 CODE COMPLETE 2026-06-02**
  - `UserRole::SPECIAL_EVENTS_ADMIN = 'special_events_admin'` added; `config/permission.php` → `role_names.special_events_admin`.
  - `RolesAndPermissionsSeeder` grants `special_events.manage` + `product.add` + `public_page.profile` (idempotent). **No `system.dashboard`** (OQ14); normal-pitch editing blocked at policy level (OQ1).
  - Scope via `user_scope` (role=`special_events_admin`, scope_type=`city`) — many-per-city. No `city_user` write (that pivot stays city-admin only).
  - ⏳ Verify: run `db:seed --class=RolesAndPermissionsSeeder` on target DB (role appears, idempotent, no existing-user change).
- [x] **T2.2 — Make it assignable in admin UI + requests.** — **🟢 CODE COMPLETE 2026-06-02**
  - Dropdowns/request auto-include the role (UserService/Automate/UserStoreRequest use "all roles" scopes — no code change needed). `Str::headline` renders it "Special Events Admin".
  - `User` model: added `syncScopeCities()`, `scopedCities()`, `assignedScopeCities()`, `isSpecialEventsAdmin()`; `syncAdminCities()` now delegates to `syncScopeCities()` + legacy `city_user` dual-write.
  - `Api\CityUserController` (index/update) + `UserController` (store/edit) are role-aware: SE admin → `user_scope` only; City admin → dual-write. `User/Edit.vue` shows the assigned-cities panel for both roles.
  - ⏳ Verify: **`tests/Feature/RolePermission/SpecialEventsAdminPermissionTest.php`** written (7 cases: seeded/assignable, scoped perms, NOT dashboard, user_scope-not-city_user, many-per-city, assignedScopeCities, dropdown). **Not yet run — Docker was down.** Run: `sail artisan test --filter=SpecialEventsAdminPermissionTest`.

### PHASE 3 — Promote Pitch location to FKs + server-side scope (fixes V1, V3)
- [x] **T3.1 — Additive pitch FK columns + data migration.** — **🟢 CODE COMPLETE 2026-06-02**
  - New `locations` table (`city_id`, name, address, lat/lng, optional `space_id` link).
  - Additive columns on `lunar_products`: `city_id`, `location_id`, `is_special_event`.
  - Idempotent backfill from meta `locations` + linked `productable` Spaces. Meta kept for dual-read.
  - Models: `App\Models\Location`, `LocationService`, `Product::city()`/`location()`/`syncLocationFks()`.
  - ⏳ Verify: `./scripts/db-etl.sh safe-migrate` then `sail artisan test --filter=PitchLocationFkTest`.
- [x] **T3.2 — Server-side scope validation on writes (V1, V3).** — **🟢 CODE COMPLETE 2026-06-02**
  - `UserScopeService` + `InScopedCityId`/`InScopedCityIds` rules.
  - `ProductEventController` asserts city scope + dual-writes FKs; `ProductEventRequest` accepts optional `city_id`/`location_id` with scope rules.
  - `SpaceStoreRequest`: scoped `city_id`, city-admin `type_id` subset, SE-admin parent options; `SpacePolicy::create` allows SE admin.
  - `SpaceEventRequest`: scoped `city_id`; `CityUserController`/`UserStoreRequest`: scoped city assignment.
  - ⏳ Verify: IDOR tests (out-of-scope city rejected) — add in T-TESTS pass.
- [x] **T3.3 — Persist `Space.city_id` on location create (FR-CA-3 gap).** — **🟢 CODE COMPLETE 2026-06-02**
  - `Space::saveFromInputs` accepts `city_id`; `SpaceService::persistCityId()` inherits from parent City node.
  - Wired in `SpaceController` store/update; migration backfills existing child spaces from parent.
  - ⏳ Verify: create child space under City node → `city_id` populated.

### PHASE 4 — Fix pitch-save bug (fixes V4, FR-PITCH-7/8)
- [x] **T4.1 — Single transactional pitch save.** — **🟢 CODE COMPLETE 2026-06-03**
  - Files: `resources/js/Pages/.../ProductEdit.vue` (collapse two-step save), `resources/js/Pages/.../ProductCreate.vue`, `modules/Booking/Http/Controllers/ProductController.php` (use `validated()`, wrap product+location+schedule in `DB::transaction`, return JSON + field errors), `ProductEventController.php`.
  - References: SEC doc V4; FRS doc §2.6, §3.6 FR-PITCH-7/8, §6.3 AC4/AC5.
  - Verify: valid save persists all (incl. city/country/location); any failure rolls back fully with a specific error (no partial pitch); save with zero ProductEvents succeeds; booking regression green.

### PHASE 5 — Application → Approval workflow (fixes V2, V5, V6; FR-ACCT-*)
- [x] **T5.1 — `role_applications` entity + branding upload.** — **🟢 CODE COMPLETE 2026-06-04**
  - Files: migration `role_applications` (`user_id?`, `requested_role`, `city_id`, `status`, branding, `reviewed_by`, `reviewed_at`, `reject_reason`); media relations; public application form/endpoint (reuse FormBuilder or dedicated); upload validation (mime allowlist, size via `ApiSettingController::maxFileSize`, re-encode, throttle, reCAPTCHA).
  - References: FRS doc §3.2 FR-ACCT-1/2, §6.1–6.2; SEC doc V6, Phase 5.
  - Verify: application persists with branding; upload validation rejects bad files.
- [x] **T5.2 — Transactional approval action.** Goal: verified + scoped + branded admin on approve. — **✅ DONE 2026-06-04** (`RoleApplicationTest` green)
  - Files: new approval controller/service; uses `UserRoleService` (role) + `user_scope` (scope) + email verify + extend `modules/Space/Services/SpaceService.php::ensureCitySpacesExist()` with branding; one-city-admin-per-city via partial unique + `SELECT…FOR UPDATE`; keep protected-email guard (`AutomateUserCreationController::validateFomEntry`).
  - References: SEC doc V2, V5, Phase 5; FRS doc §3.2 FR-ACCT-3/4, §6.1 AC1–AC5; RBAC doc R5; **Blocked by OQ3, OQ13.** Feature-flag the rollout.
  - Verify: approve → role+scope+verified+branded page; reject → no grant + reason; duplicate/concurrent city-admin approval blocked; SE-admin many-per-city allowed; protected-email rejected.

### PHASE 6 — Pitch field UX + 14-day Special Events rule (FR-PITCH-3/5/6/9, FR-SE-2/3)
- [x] **T6.1 — Unified pitch form + label/field changes.** — **🟢 CODE COMPLETE 2026-06-04**
  - Files: `resources/js/Pages/.../ProductForm.vue`, `ProductCreate.vue`, `ProductEdit.vue`; `resources/lang/...` copy.
  - Changes: all fields visible before first save; rename "Duration"→"Timeslot duration" and **swap** with "Pitch date range"; "Weekly hours"→"Weekly days and hours" + new help copy; **consolidate duplicate timezone** (`pitch_timezone` vs schedule `timezone`, FR-PITCH-9); disable gallery (confirm logo/cover via **OQ11**).
  - References: FRS doc §2.6, §3.6 FR-PITCH-3/5/6/9/10, §6.3 AC1; **Blocked by OQ11.**
  - Verify: single timezone persists; label/order snapshots; create form shows all fields.
- [x] **T6.2 — 14-day cap + visible-vs-bookable for special events.** — **🟢 CODE COMPLETE 2026-06-04**
  - Files: `ProductEventRequest`/`ProductEventCrudRequest` (≤14-day validation), `modules/Booking/Services/ProductEventService.php::maxBookableDate()`, date pickers; visibility decoupled from bookability.
  - References: FRS doc §3.3 FR-SE-2/3, §6.3 AC6/AC7; SEC doc Phase 6; **Blocked by OQ5** (overlap).
  - Verify: >14d rejected, ≤14d accepted; special-events pitch visible year-round but only bookable in window.

### PHASE 7 — Landing nav + Location-type removal + map pins + search (FR-NAV-*, FR-CA-2, FR-SE-4)
*Blocked by OQ6/OQ7/OQ8/OQ9 (hierarchy & nav model).*
- [x] **T7.1 — Fix slug resolution & broken levels (FR-NAV-2).** — **🟢 CODE COMPLETE 2026-06-02** (php -l + lint clean; behavior-preserving fallbacks). _Verify pending: manual browse of populated AND empty levels._
  - Files: `modules/Space/Services/PageSpaceService.php` (full ancestor-path resolution, not last-segment), `modules/Space/Entities/PageTranslation.php` (`unique_key` accessor / null-segment handling), `modules/Space/Http/Controllers/Frontend/SpaceController.php` (redirect param shape).
  - References: FRS doc §2.1, §3.1 FR-NAV-2, §6.5 AC2; SEC doc §8.4.
  - Verify: populated AND empty levels render (no 404 / unique-ID error).
- [x] **T7.2 — Data-driven Country→City→Pitch→Events drill-down (FR-NAV-1).** — **✅ DONE 2026-06-04** (`LandingNavTest` green)
  - Files: `app/Services/MenuService.php`, theme navbar, `PageSpaceService::getLeaves()`; bind to canonical hierarchy.
  - References: FRS doc §3.1 FR-NAV-1, §6.5; **Blocked by OQ6/OQ7/OQ9.**
  - Verify: drill-down generated from data; cached; correct labels.
- [x] **T7.3 — Remove/lock Location type + enforce sibling rules (FR-CA-2).** — **✅ DONE 2026-06-04** (`SpaceHierarchyTest` green)
  - Files: `SpaceForm.vue` (type dropdown removed), `SpaceHierarchyService`, `SpaceStoreRequest`, `SpaceController` store/update.
  - References: FRS doc §2.4, §3.4 FR-CA-2, §6.6 AC1/AC3; **Blocked by OQ6/OQ8.**
  - Verify: no type dropdown; sibling-city / non-city-parent / out-of-scope rejected.
- [x] **T7.4 — Color-coded map pins + working search (FR-NAV-3/4).** — **✅ DONE**
  - Files: `database/migrations/2026_06_04_100000_update_event_calendars_view_for_search_and_pins.php`, `EventsCalendarService`, `EventCalendar`, `CountryService`, `EventsCalendar.vue`, `EventsCalendarRequest`.
  - Verify: search returns normal **and** special events for valid criteria; distinct pins; both types visible when browsing a city. `sail test tests/Feature/EventsCalendarSearchTest.php` — 4 pass.

### Cross-cutting (carry through all phases)
- [ ] **T-PERF-CANCEL — Performer cancellation semantics.** Blocked by **OQ4**. Files: `EventCanceled` event + `CancelUpcomingOrOngoingBookings` listener, `OrderService`. Only implement after OQ4 answered.
- [ ] **T-TESTS — Test suite expansion** (per SEC doc §9 "Suggested tests"): role/scope IDOR attempts, atomic save rollback, 14-day cap, visible-not-bookable, approval (verify/scope/one-per-city/concurrency/upload/protected-email), nav empty-level, search country-code normalization, seeder idempotency.

---

## 8. Quick sequencing summary

```
Phase 0 (role registry+seeder+assignment)  → 1 (scope+City Admin)  → 2 (SE Admin, unassigned)
   → 3 (pitch FKs+server scope)  → 4 (atomic pitch save)
   → 5 (application→approval, flag-gated)
   → 6 (pitch UX + 14-day)  → 7 (nav + type removal + pins + search)
```
**Answer before Phase 3:** OQ6, OQ8, OQ9, OQ10. **Do not start a task whose blocker OQ is unanswered.**

---

*End of guide. For the “why” behind any task, follow its References to the FRS/SEC/ROLE/RBAC docs and the cited source files.*
