# New Client Requirements — Vulnerabilities, Scalability & Phased Implementation Plan

> Generated: 2026-06-01
> Status: **Planning / documentation only. No application code was modified.**
> Companion to: [`new-requirements-frs-and-refactor-plan.md`](./new-requirements-frs-and-refactor-plan.md) (Executive Summary, Current-State Mapping, FRS, Role Matrix, ERM, User Stories, Open Questions).
> Builds on: [`user-approval-roles-rbac-map.md`](./user-approval-roles-rbac-map.md) (risk IDs R1–R11) and [`user-approval-roles-refactor-plan.md`](./user-approval-roles-refactor-plan.md) (PR 1–6 foundation).

This document covers brief sections **7 (Vulnerabilities & Security)**, **8 (Scalability)**, and **9 (Phased refactor / implementation plan)**, plus a consolidated Open-Questions reference.

---

## 7. Vulnerabilities & Security Analysis

New vulnerability IDs **V1–V9** extend the existing R1–R11. Each: description, evidence (verified file:line), severity, and mitigation. The requirements that **introduce or expose** each risk are noted.

### V1 — No server-side scope on pitch city/location (authorization gap / IDOR)
- **Severity: High.** Exposed by FR-PITCH-4, FR-CA-4, FR-SE-4 ("city options limited to cities created by X").
- **Evidence:** Pitch location is a free-text meta string, not a FK. `ProductForm`'s `FieldsetLocation` does **not** pass `restricted-cities` (contrast `SpaceForm.vue:57` which does pass `:restricted-cities="userCities"`). `ProductEventController@update` (`modules/Booking/Http/Controllers/ProductEventController.php:51-94`) writes `location.city`/`country_code` from input with **no check** against the actor's `adminCities`; `city_id` is never persisted. Listing is scoped (`ProductService::getRecords`, `modules/Ecommerce/Services/ProductService.php:32-66`) but **writes are not**.
- **Attack:** a City/SE Admin POSTs a pitch with a city outside their scope; the server accepts it. Cross-scope content injection / privilege over other cities.
- **Mitigation:** Promote pitch location to `city_id`/`location_id` FKs (ERM §5); validate server-side that the chosen `city_id` ∈ the actor's scope (and `location_id` belongs to that city); reject otherwise. Enforce in a FormRequest rule (`Rule::in(scopedCityIds)`), not just the UI.

### V2 — Approval auto-creates unverified, unscoped, privileged accounts (extends R5)
- **Severity: High.** Introduced by FR-ACCT-1/2/3 (modeling approval on the street-performer FormBuilder path) now minting **admin** accounts.
- **Evidence:** `AutomateUserCreationService::createOrUpdateUser()` (`modules/FormBuilder/Services/AutomateUserCreationService.php:315-335`) creates users with a random password and **never** calls `verifiyEmail()`/`markEmailAsVerified()` (contrast `UserController.php:126`). The path **never syncs `city_user`** scope. Role comes from a `FormMappingRule` (`group='role'`); `getRoleOptions()` uses `withoutAdmin()` so it would happily offer `city_administrator`/`special_events_admin`.
- **Attack / failure modes:** an approved admin has `email_verified_at = null` → blocked at `verified` middleware (confusing), OR a privileged role assigned with **no scope** → either useless or (worse, combined with V-scope gaps) broadly capable. Reusing the generic form path for privilege escalation is fragile.
- **Mitigation:** Do **not** mint admins via the generic FormBuilder path. Build a dedicated `role_applications` approval action that, in one transaction: assigns role via `UserRoleService`, syncs scope, **verifies email** (or sends verification), provisions the city page, and records `reviewed_by`/`reviewed_at`. Keep the protected-email guard (`validateFomEntry`).

### V3 — Mass-assignment / IDOR on `type_id`, space-event `city_id`, and user `cities`
- **Severity: High.** Exposed by FR-CA-2/3, FR-ACCT-6.
- **Evidence:**
  - `SpaceStoreRequest` validates `type_id` against **all** types (`modules/Space/Http/Requests/SpaceStoreRequest.php:45-48`), not the city-admin subset (`SpaceService::cityAdminTypeOptions()` is UI-only). A city admin can create a Space of any type (e.g. a "Country").
  - `EventService` mass-assigns `city_id` from raw input (`modules/Space/Services/EventService.php:113`) with **no** validation rule in `SpaceEventRequest`.
  - `CityUserController@update` (`app/Http/Controllers/Api/CityUserController.php:17-26`) and `UserController@store` (`app/Http/Controllers/UserController.php:132-138`) sync **arbitrary** `cities` with no role/scope pairing and no per-city-admin uniqueness; `UserStoreRequest` has **no `cities` rules** and `authorize()` returns `true`.
- **Mitigation:** Validate every scope-bearing field server-side against the actor's allowed set (`Rule::in(...)`); add `city_id` rules to `SpaceEventRequest`; gate `cities` sync to scoped roles only and validate IDs against allowed cities; register `SpaceEventPolicy` (see V7).

### V4 — Pitch-save partial-commit (data integrity)
- **Severity: High.** Directly the client-reported bug; addressed by FR-PITCH-8.
- **Evidence:** `ProductEdit.vue:540-593` saves in **two non-transactional steps**: step 1 `POST ProductController@update` (pitch row persists), step 2 `PUT ProductEventController@update` (location + schedule). Step 2 requires `pitch_started_at/ended_at/pitch_timezone` (`modules/Booking/Http/Requests/ProductEventRequest.php:19-21`); if empty → 422 → `oopsAlert()` while the **partial pitch with no city/country** survives. `ProductController@store` uses `$request->all()` (no `validated()`), and create only sets location when `space_id` is present.
- **Mitigation:** Single transactional save endpoint (wrap product + location + schedule in `DB::transaction`), return JSON, surface field-level errors. Persist `city_id`/`location_id` as FKs. Add a server-side guard so a pitch is never left without its mandatory location/city.

### V5 — No "one City Admin per city" + concurrent-approval race
- **Severity: Medium.** Exposed by FR-ACCT-4.
- **Evidence:** `city_user` is unique only on `(user_id, city_id)` (`database/migrations/2025_12_03_021806_create_city_user_table.php:28`). No per-city uniqueness; no transactional guard. Two approvals for the same city would both succeed.
- **Mitigation:** Partial unique index `(scope_type, scope_id) WHERE role = 'city_administrator'` on the generalized scope table (or a guarded transaction with `SELECT ... FOR UPDATE`). Resolve transfer/replace semantics (Open Question 3) before enforcing.

### V6 — File/media upload risk for logos/banners (application branding)
- **Severity: Medium.** Introduced by FR-ACCT-1/3 (applicants upload branding pre-approval).
- **Evidence:** Media flows through `MediaService`/Cloudinary; FormBuilder file fields and Space media already exist, but **public applicants** uploading logos/banners is a new untrusted surface. No requirement-level constraint on type/size/dimensions stated.
- **Mitigation:** Strict server-side validation (mime allowlist, max size via existing `ApiSettingController::maxFileSize`, image re-encode/strip metadata), store under the application record, scan/limit; don't expose raw uploads publicly until approved; rate-limit the public application endpoint (reCAPTCHA already applies to FormBuilder).

### V7 — Unregistered `SpaceEventPolicy` (inconsistent enforcement)
- **Severity: Medium.**
- **Evidence:** `app/Policies/SpaceEventPolicy.php:106-116` correctly scopes via `isCityAdmin($spaceEvent->city_id)` — but it is **not registered** in `AuthServiceProvider` (only `ProductEventPolicy` is, `app/Providers/AuthServiceProvider.php:43-55`). Space-event authorization currently runs through `CanManageEvent` middleware (`modules/Space/Http/Middleware/CanManageEvent.php:10-16`) keyed on space `update`, **not** event city.
- **Mitigation:** Register `SpaceEventPolicy`; route space-event authorization through it; add `city_id` validation.

### V8 — Hardcoded role strings + orphaned `CityAdministratorSeeder` (environment drift) (extends R2, R4, R8)
- **Severity: Medium.**
- **Evidence:** `CityAdministratorSeeder` is **not** in `DatabaseSeeder` (`database/seeders/DatabaseSeeder.php:17-39`) → fresh installs lack the role. `'city_administrator'` is hardcoded in ~14 sites (User, MenuService, LoginResponse, ProductEventPolicy, SpacePolicy, SpaceController, SpaceStoreRequest, SpaceService, ProductPolicy, ProductService, ProductSpaceService, ProductController, the seeder, and `User/Edit.vue:344`). `Gate::after` uses literal `'Super Administrator'` (`app/Providers/AuthServiceProvider.php:68-70`). `city_administrator` is **absent** from `config/permission.php role_names`. The seeder grants `system.dashboard`, which makes the `MenuService`/`LoginResponse` city-admin branches (`&& !$user->can('system.dashboard')`) **dead code** — so enforcement differs by environment/data.
- **Mitigation:** Execute PR 1–2 of the role refactor (config/enum registry + idempotent consolidated seeder registered in `DatabaseSeeder`) **before** adding roles; replace literals with the registry; resolve the `system.dashboard` intent (Open Question 14).

### V9 — Capability-without-scope for Administrator (privilege confusion)
- **Severity: Low–Medium.** Exposed by FR-ADMIN-1.
- **Evidence:** Scoped policies (`ProductPolicy::canManageProductSpace` `modules/Ecommerce/Policies/ProductPolicy.php:62-81`, `SpaceEventPolicy`, `SpacePolicy::manage`) key on `adminCities`/`isCityAdmin`. An `Administrator` (capability `*.*`) with **no** `city_user` rows would be **denied** scoped actions, contradicting "Admin can do everything a city admin can".
- **Mitigation:** In every scoped policy, short-circuit `true` for Administrator/Super Administrator (via the role registry), or treat them as in-scope for all cities. Add tests.

### Security summary table

| ID | Risk | Severity | Primary mitigation |
|---|---|---|---|
| V1 | Pitch city/location not scoped server-side (IDOR) | High | FK + server-side `Rule::in(scopedCities)` |
| V2 | Approval mints unverified/unscoped admins (R5+) | High | Dedicated transactional approval; verify email; sync scope |
| V3 | Mass-assignment: `type_id`, event `city_id`, user `cities` | High | Validate against actor's allowed set |
| V4 | Pitch-save partial commit | High | Single transactional save + JSON + FK location |
| V5 | No one-admin-per-city + race | Medium | Partial unique index + transaction |
| V6 | Public branding upload surface | Medium | Mime/size validation, re-encode, throttle |
| V7 | Unregistered `SpaceEventPolicy` | Medium | Register + route through it |
| V8 | Hardcoded roles + orphaned seeder | Medium | Registry + consolidated seeder (role refactor PR 1–2) |
| V9 | Administrator capability without scope | Low–Med | Bypass scope for admin/super in policies |

---

## 8. Scalability Analysis

Where the current design won't scale or is brittle for these features, with recommended patterns.

### 8.1 Role/permission hardcoding & per-module seeders
- **Problem:** Role strings are scattered across ~14+ files (V8); permissions are seeded in **5 disconnected places** (core `PermissionSeeder` + `UserAndPermissionSeeder` + `modules/{Space,Ecommerce,FormBuilder}/.../PermissionSeeder`), and module permissions are assigned **only to Administrator**. Adding a third/fourth scoped role multiplies the edit sites and the chance of environment drift (the `city_administrator` seeder is already orphaned).
- **Recommendation:** Centralized role registry (`UserRole` enum + `config/permission.php role_names`) and **one declarative `[role => permissions[]]` map** seeded idempotently (role-refactor PR 1–2). Adding a role becomes a single map entry. This is a prerequisite for the two new roles.

### 8.2 Scope coupled to City Admin only (`city_user`)
- **Problem:** Scope is hardwired to City Admin via `city_user` + literal `isCityAdmin`/`hasRole('city_administrator')` checks. Special Events Admin needs the **same** scope mechanics with **different cardinality** (many-per-city). Bolting on a second pivot per role doesn't scale (every future scoped role = new table + new helpers + new policy branches).
- **Recommendation:** Generalize to a **role-aware `user_scope`** table `(user_id, role, scope_type, scope_id)` with helper `User::scopesFor($role)` and a single `inScope($role, $scopeType, $scopeId)` check; per-role uniqueness via partial indexes. Migrate `city_user` forward (dual-read during transition). This makes "scoped role N" a config concern, not a schema change.

### 8.3 Pitch location as free-text meta
- **Problem:** City stored as a **string** in product `locations` meta defeats indexing, scoping, and the navigation drill-down (you can't reliably "list pitches in city X" or enforce "city created by admin"). The `event_calendars` view already does fragile JSON extraction (`location::json#>>'{0,city}'`) and a `country_code` length mismatch (2 vs 3) silently breaks search.
- **Recommendation:** Promote to `city_id`/`location_id` FKs with indexes; back the calendar/search with indexed columns instead of JSON path extraction; normalize `country_code`.

### 8.4 Navigation / menu generation
- **Problem:** The Country→City→Pitch drill-down is **manual** (CMS `menu_items` + Space tree). There is no data-driven generation, so adding cities/pitches requires manual menu edits, and slug resolution uses **last-segment-only** lookup (collision-prone at scale) with `null`/`uniqueKey` fallbacks that 404.
- **Recommendation:** Generate the city submenu and pitch lists from the canonical hierarchy (cached); resolve URLs by **full ancestor path**; ensure every node has a stable, unique, non-null slug; cache header menus (already partially cached in `HandleInertiaRequests`).

### 8.5 Map pin rendering & calendar for many pitches
- **Problem:** `EventsCalendar.vue` creates a `google.maps.Marker` per record with `MarkerClusterer`; all one style. With many pitches/events and per-type pins this needs clustering tuning and server-side viewport/bounds filtering rather than fetching all.
- **Recommendation:** Server-side bounding-box + pagination for markers; lightweight marker payloads (id, lat/lng, type/color); cluster by type; lazy-load detail on click.

### 8.6 Calendar/timeslot generation
- **Problem:** Availability is computed on demand (`EventService::availableTimes()`) by expanding schedule rules minus booked events. Fine per-pitch, but the public landing listing "upcoming pitches/events per city" across many pitches could be O(pitches × days).
- **Recommendation:** Materialize/refresh upcoming availability (or use the `event_calendars` view extended with indexed city/type columns); cache per city; precompute "has upcoming availability" flags for listings.

### 8.7 Search
- **Problem:** Search is booking-centric (`availableTypes()` = `['booked_event']`), JSON-based filtering, and constrained by the country-code mismatch.
- **Recommendation:** Index city/country/type/date on the calendar source; include all requested event types; consider a dedicated search index if volume grows. Add DB indexes on `events.booked_at`, `events.status`, pitch `city_id`, and the calendar view's filter columns.

---

## 9. Phased Refactor / Implementation Plan

Dependency-ordered. Each phase: **goal · changes · risk · revertibility · tests**. This **extends** the PR sequence in [`user-approval-roles-refactor-plan.md` §7](./user-approval-roles-refactor-plan.md) — Phase 0 below *is* that plan's PR 1–3, which MUST land first.

> **Guiding rules (inherited):** backward-compatible first; additive then subtractive; idempotent seeders; one behavior change per PR; never rename/delete existing roles or drop `city_user`/`spaces.type_id`/meta-`locations` in the same release that introduces replacements.

### What to change FIRST (foundational, low-risk)
Phase 0 → 1 → 2. Do **not** touch pitch save, navigation, or the Location-type removal until the role/scope foundation and FK promotion are in place.

### What NOT to touch yet
- The dual-portal session logic (`home_url`, `EnsureLoginFrom*`, `AuthenticateLoginAttempt`).
- Existing role **names** in the DB (`Super Administrator`, `Administrator`, `Author`, `Performer`, `city_administrator`).
- `enable_wildcard_permission` and Spatie internals.
- The FormBuilder mapping schema (reuse; don't restructure).
- Lunar/Ecommerce internals beyond the pitch FK additions.
- Do not drop `city_user`, `spaces.type_id`, or meta-`locations` until all readers are migrated.

---

### Phase 0 — Role/permission foundation *(= refactor-plan PR 1–3)*
- **Goal:** Single source of truth for roles/permissions + unified assignment, zero behavior change.
- **Changes:** `UserRole` registry + extend `config/permission.php role_names` (incl. `city_administrator`); idempotent consolidated `RolesAndPermissionsSeeder` registered in `DatabaseSeeder` (fixes orphaned seeder, V8/R2); `UserRoleService::assignRole`; swap read-only literals (`Gate::after`, `RegisteredUserController`, `User::isCityAdmin*`).
- **Risk:** Low–Med (prod seed). **Revertible:** Yes.
- **Tests:** registry resolvable; `migrate:fresh --seed` parity incl. `city_administrator`; seeder idempotency; `UserRoleService` unit tests; existing `tests/Feature/RolePermission/*` stay green.

### Phase 1 — Generalize scope + formalize City Admin
- **Goal:** Make scope role-aware and consistent; fix the City-Admin enforcement gaps (V7, V8 dead-code, V9) without new behavior for users.
- **Changes:** Introduce `user_scope` table (or document reuse of `city_user`); migrate `city_user` forward (dual-read); register `SpaceEventPolicy` (V7); make scoped policies short-circuit for Administrator/Super (V9); resolve `system.dashboard` intent for city admin (Open Question 14) and align/remove the dead `MenuService`/`LoginResponse` branches; replace `city_administrator` literals with the registry.
- **Risk:** Medium (touches authorization). **Revertible:** Yes (keep `city_user` reads).
- **Tests:** city-admin scope on Space index/store/update/destroy (incl. cross-city denial); `SpaceEventPolicy` registration + `city_id` enforcement; Administrator can act on all cities; existing `CityAdministratorTest` green.

### Phase 2 — Add Special Events Admin role (seeded, unassigned)
- **Goal:** New role exists and is assignable with scope; no users assigned yet (no behavior change).
- **Changes:** Add `special_events_admin` to registry + permission map (seeded idempotently, unassigned first); wire scope via `user_scope`; surface in `UserService::getRoleOptions`/`AutomateUserCreationService::getRoleOptions`/`UserStoreRequest::getRoleIds`; extend `User/Create.vue`/`Edit.vue` city UI to the new role; many-per-city allowed.
- **Risk:** Medium. **Revertible:** Yes (unassign + leave seeded).
- **Tests:** new `tests/Feature/RolePermission/SpecialEventsAdminPermissionTest.php` (allowed vs denied, scope); role appears in all dropdowns; assignable via `UserController` + scope sync; login/dashboard behavior per Open Question 14.

### Phase 3 — Promote Pitch location to FKs + server-side scope *(fixes V1, V3)*
- **Goal:** Pitches have real `city_id`/`location_id`; writes are scope-validated server-side.
- **Changes:** Additive `products.city_id`/`location_id` (+ `is_special_event`); forward data migration from meta-`locations`; validate `city_id`/`location_id` ∈ actor scope in `ProductRequest`/`ProductEventRequest` (`Rule::in`); make location **required**; fix `Space` `city_id` persistence (FR-CA-3); restrict `type_id`/`parent_id` server-side (V3); validate space-event `city_id`.
- **Risk:** Medium–High (data migration). **Revertible:** Forward-only; keep meta-`locations` dual-read.
- **Tests:** out-of-scope `city_id`/`location_id`/`type_id` rejected; pitch requires location; data-migration correctness; listing still scoped.

### Phase 4 — Fix pitch-save bug *(fixes V4, FR-PITCH-7/8)*
- **Goal:** Atomic, reliable pitch save; no silent partial pitch.
- **Changes:** Single transactional save endpoint (product + location + schedule in `DB::transaction`) returning JSON with field errors; use `validated()` in `store`; allow saving with no ProductEvents; ensure failure persists nothing.
- **Risk:** Medium. **Revertible:** Yes.
- **Tests:** valid save persists all incl. city/country/location; any step failure rolls back fully and returns a specific error; save with zero events succeeds; regression on existing bookings.

### Phase 5 — Application → Approval workflow *(fixes V2, V5, V6; FR-ACCT-*)*
- **Goal:** Self-service applications + audited approval that provisions verified, scoped, branded admins.
- **Changes:** New `role_applications` table + media for branding (V6 validation); approval action (transactional): `UserRoleService` role assign + scope sync + **email verify** + auto-provision city page (extend `SpaceService::ensureCitySpacesExist()` with branding) + `reviewed_by/at`; enforce one-City-Admin-per-city (partial unique + transaction, V5); resolve transfer/replace (Open Question 3); keep protected-email guard.
- **Risk:** High (privileged provisioning). **Revertible:** Application records are additive; gate behind a feature flag.
- **Tests:** approve → verified+scoped+branded; reject → no grant; duplicate/concurrent city-admin approval blocked; SE-admin many-per-city allowed; upload validation; protected-email rejection.

### Phase 6 — Pitch field UX + 14-day Special Events rule *(FR-PITCH-3/5/6/9, FR-SE-2/3)*
- **Goal:** Pitch form matches the requested fields; special events capped to 14 days and decoupled visibility/bookability.
- **Changes:** Unify create+edit form (all fields visible); rename/reorder "Timeslot duration" ↔ "Pitch date range"; "Weekly days and hours" + new copy; consolidate the duplicate timezone (FR-PITCH-9); disable gallery (confirm logo/cover intent, Open Question 11); 14-day cap in `ProductEventRequest`/`ProductEventCrudRequest` + `maxBookableDate()` + date pickers; year-round visibility with windowed bookability.
- **Risk:** Medium. **Revertible:** Yes (mostly UI + validation).
- **Tests:** >14-day window rejected, ≤14 accepted; special-events pitch visible outside window but not bookable; single timezone persisted correctly; copy/label snapshots.

### Phase 7 — Landing navigation + Location-type removal + map pins + search *(FR-NAV-*, FR-CA-2, FR-SE-4)*
- **Goal:** Working Country→City→Pitch→Events drill-down; no broken levels; type-less locations; color-coded pins; working search.
- **Changes:** Data-driven city submenu + pitch/event lists from the canonical hierarchy; fix slug resolution (full-path, no null/`uniqueKey` segments) and the redirect param shape (FR-NAV-2); remove/lock the Location `type` dropdown and enforce "child-of-city / no sibling city" (FR-CA-2, depends on Open Question 6/8 hierarchy decision); add `is_special_event` to `event_calendars` + per-type marker color; fix search (`availableTypes` include requested types, normalize country-code length, sensible default window).
- **Risk:** Medium–High (public-facing + hierarchy decision). **Revertible:** Mostly yes; hierarchy/type changes need care.
- **Tests:** drill-down for populated and **empty** levels (no 404/unique-ID); search returns normal + special events for valid criteria; map shows distinct pins; location creation rejects sibling-city / non-city-parent / out-of-scope.

### Sequencing & risk summary

| Phase | Touches | Behavior change | Risk | Revertible |
|---|---|---|---|---|
| 0 | role registry, seeder, assignment | None | Low–Med | Yes |
| 1 | scope generalization, policies | None for users | Med | Yes |
| 2 | new SE-Admin role (unassigned) | New role only | Med | Yes |
| 3 | pitch FK + server scope | Validation tightened | Med–High | Forward-only |
| 4 | pitch save | Bug fix | Med | Yes |
| 5 | application/approval | New workflow | High | Flag-gated |
| 6 | pitch UX + 14-day | Field/validation | Med | Yes |
| 7 | nav + type removal + pins + search | Public-facing | Med–High | Partial |

**Do first:** Phase 0 → 1 → 2 (foundation + roles), then 3 → 4 (pitch data + save), then 5 (approval), then 6 → 7 (UX + public).

### Suggested tests to add/update (cross-cutting)
- `tests/Feature/RolePermission/SpecialEventsAdminPermissionTest.php` (new) + extend `CityAdministratorTest` with HTTP/scope cases (Space index/store/update/destroy, cross-city denial, `type_id`/`parent_id`/`city_id` IDOR attempts).
- Booking/pitch: scoped `city_id`/`location_id` rejection; mandatory location; atomic save rollback; save with no events; 14-day cap; visible-but-not-bookable special events.
- Approval: verified+scoped+branded on approve; reject; one-admin-per-city + concurrency; upload validation; protected-email guard.
- Navigation/search: empty-level rendering (no 404); search returns normal + special; country-code normalization.
- Seeder/registry: `DatabaseSeeder` includes the consolidated seeder; idempotent re-run; existing users retain roles.

---

## Consolidated Open Questions (must be confirmed with client)

Full detail (with code rationale and labeled assumptions) is in [`new-requirements-frs-and-refactor-plan.md` §7](./new-requirements-frs-and-refactor-plan.md#7-open-questions--assumptions). Summary:

**Client-stated (1–7):**
1. Can a Special Events Admin edit normal pitches?
2. Can a City Admin edit pitches created by another City Admin (ownership change)?
3. City-admin application for a city that already has one — reject / transfer / replace?
4. Can a performer cancel a booking? Does the event disappear or return to an available timeslot?
5. Can Special Events pitches overlap with normal pitches?
6. Confirm the canonical hierarchy: Country → City → Location → Pitch → Timeslot → Event.
7. Confirm desired landing navigation Country → City → Pitch → Events (and fixed menu labels).

**Surfaced by code review (8–14):**
8. "Location" model: new dedicated `locations` table, or `Space` with `type` hidden/locked?
9. Reconcile dual City/Country models (`cities`/`countries` vs Space "City"/"Country" nodes) — which is canonical?
10. Scope model: generalize `city_user` → `user_scope`, or add a parallel SE-admin pivot?
11. Pitch "logos and cover" — pitches have none today (only gallery); does this mean the city-page/Space branding?
12. "Only street performers can create events?" — admin ProductEvent windows or performer bookings? (No `street_performer` role exists.)
13. Should `Administrator` (not just Super Admin) be globally scoped, and may Administrators approve applications?
14. Do City/SE Admins need full `system.dashboard` access (today's seeder grants it, making UX branches dead code)?

**Planning assumptions (flagged, need confirmation):** canonical `countries→cities→locations→pitches` hierarchy with Space tree as presentation layer; generalized `user_scope`; role foundation ships before new roles with snake_case `special_events_admin`; approval reuses/extends `ensureCitySpacesExist()` + new `role_applications`.
