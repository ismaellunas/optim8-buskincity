# New Client Requirements — FRS, Current-State Mapping, Role Matrix & ERM

> Generated: 2026-06-01
> Status: **Planning / documentation only. No application code was modified.**
> Scope: Maps the new BuskinCity client requirements (landing navigation, account/approval, Special Events Admin, City Admin, performer booking, pitch creation, admin) onto the actual code, then specifies the target behavior.
> Companion document (sections 7–9): [`new-requirements-security-scalability-and-phasing.md`](./new-requirements-security-scalability-and-phasing.md) — Vulnerabilities & Security, Scalability, and the Phased Implementation Plan.
> **Daniel email source (performer + pitch creation):** [`daniel-email-performer-and-pitch-requirements.md`](./daniel-email-performer-and-pitch-requirements.md)
> Grounded in: [`user-approval-roles-rbac-map.md`](./user-approval-roles-rbac-map.md) (risk IDs R1–R11), [`user-approval-roles-refactor-plan.md`](./user-approval-roles-refactor-plan.md), and the booking/space/form-builder/auth/user-management execution-flow docs in this folder. Code claims below were re-verified against the current source.

---

## Contents

1. [Executive Summary](#1-executive-summary)
2. [Current-State Mapping](#2-current-state-mapping)
3. [Formal Functional Requirements Specification (FRS)](#3-formal-functional-requirements-specification-frs)
4. [Role Matrix](#4-role-matrix)
5. [Entity Relationship Model (ERM)](#5-entity-relationship-model-erm)
6. [User Stories with Acceptance Criteria](#6-user-stories-with-acceptance-criteria)
7. [Open Questions / Assumptions](#7-open-questions--assumptions)

> Sections **7 (Vulnerabilities)**, **8 (Scalability)** and **9 (Phased plan)** from the brief live in the [companion document](./new-requirements-security-scalability-and-phasing.md). Open Questions are duplicated at the end of both files for convenience.

---

## 1. Executive Summary

### 1.1 What the change really is

The client wants to turn BuskinCity's loosely-coupled CMS + booking engine into a **structured, geographically-scoped marketplace** with a clear public drill-down (**Country → City → Pitch → Events**) and two **self-service, approval-gated, city-scoped admin roles** (City Admin and a new Special Events Admin) that can stand up their own city/location/pitch content the same way street performers are auto-provisioned today.

The single most important finding from the code review is that **the domain vocabulary in the requirements does not match the code 1:1**, and several requirements assume entities/relationships that **do not exist as first-class records today**:

| Client term | Actual code reality | Implication |
|---|---|---|
| **Pitch** | A Lunar `Product` (`product_type = "Event"`) with location data stored in a **JSON meta key `locations`** (city is a *string*, not a FK) and scheduling in a morphed `Schedule`. Source: `modules/Booking/Http/Controllers/ProductController.php`, `modules/Ecommerce/Entities/Product.php`. | "Pitch must belong to a city created by an admin" cannot be enforced today because the pitch stores a **free-text city string**, not `city_id`. |
| **Timeslot** | **Not a stored row.** Computed on demand from `Schedule → ScheduleRule → ScheduleRuleTime` minus booked `events`. Source: `modules/Booking/Services/EventService.php`. | A "14-day bookable window" must be enforced in validation + availability math, not by a column. |
| **Event** (booked timeslot) | A row in the **`events`** table created by `OrderService::bookEvent()` (also writes `orders` + `order_lines`). Source: `modules/Ecommerce/Services/OrderService.php`. | The requirement "a booked timeslot becomes an event" already matches reality — good. |
| **"Event" in the admin "Events" tab** | A `ProductEvent` row (`product_events`) = an **admin-defined bookable window/campaign** under a pitch. Distinct from a booked `Event`. Source: `modules/Booking/Http/Controllers/ProductEventCrudController.php`. | The word "event" is overloaded in the codebase (`ProductEvent` vs `Event`). The FRS keeps them distinct. |
| **Location** | A **`Space`** (`modules/Space/Entities/Space.php`); the Space module's UI literally relabels "Space" → "Location" (`resources/lang/en/space_term.php`). | "Location should not have a type" means redesigning the Space `type_id` field. |
| **Location "Type"** dropdown (None / Country / Pitch / City / Special Events / Festivals) | A `global_options` lookup (`type = 'space'`) referenced by `spaces.type_id`. Seeded in `database/seeders/GlobalOptionSeeder.php`. | "Type" is *the* mechanism that today distinguishes Country/City/Pitch nodes — it cannot simply be deleted without replacing the hierarchy model. |
| **City** | **Two parallel concepts**: (a) `App\Models\City` (`cities` table + `city_user` scope pivot) and (b) a `Space` row whose `type` is "City". They are only loosely linked via `spaces.city_id` (nullable) and `SpaceService::ensureCitySpacesExist()`. | This duplication is the root of much of the navigation/scope confusion and must be reconciled. |
| **Country** | **Two parallel concepts**: `App\Models\Country` (`countries` table, alpha2/alpha3) and `Space` rows of type "Country" (e.g. "Netherlands", "Sweden"). | Landing nav uses the Space-tree countries; scope/validation uses `country_code` strings. |

### 1.2 Biggest architectural impacts

1. **Reconcile the dual hierarchy.** Today there are *two* trees: the `cities`/`city_user` operational/scope model and the nested-set `spaces` tree (Country → City → Pitch, `maxParentDepth = 2`). The requirements (Country → City → Location → Pitch → Timeslot → Event, "locations are siblings of cities", "a city cannot have a sibling city", "pitch must have a location") **only make sense against one canonical hierarchy**. This is the central modeling decision (see §5 and Open Question 6).
2. **Promote "Pitch" to a first-class, city-scoped, location-bound record.** Pitch location must move from free-text meta to real FKs (`city_id`, `location_id`/`space_id`) so server-side scope rules ("cities created by X", "must have a location") are enforceable.
3. **Introduce two scoped roles on top of the agreed role/registry refactor.** Formalize `city_administrator` and add `special_events_admin`, both scoped via a generalized user↔scope assignment (today only `city_user` exists). This builds directly on PR 1–PR 4 of [`user-approval-roles-refactor-plan.md`](./user-approval-roles-refactor-plan.md).
4. **Build a real application → approval workflow for admin roles.** The client explicitly models this on the street-performer auto-setup, i.e. the FormBuilder "Automate User Creation" path — but that path **auto-creates users without verifying email (R5)** and **never assigns city scope**, and would now mint *privileged* admin accounts. This needs an explicit, audited approval record.
5. **Split the "Booking" top-level menu** into "Pitches" and "Bookings", remove the Location "type" concept, and add color-coded map pins + a working Country→City→Pitch→Events drill-down and search — all of which touch the navigation/menu generation and the events-calendar page-builder component.

### 1.3 Top risks / vulnerabilities (full analysis in the [companion doc §7](./new-requirements-security-scalability-and-phasing.md#7-vulnerabilities--security-analysis))

| # | Risk | Severity |
|---|---|---|
| **V1** | **No server-side scope on pitch city/location.** Pitch city is a free-text meta string and `ProductForm`'s location fieldset does **not** pass `restricted-cities`; `ProductEventController@update` does not validate the city against the actor's `adminCities`. A city/special-events admin can set any city → cross-scope IDOR. | **High** |
| **V2** | **FormBuilder approval auto-creates unverified privileged users (extends R5).** `AutomateUserCreationService::createOrUpdateUser()` never calls `verifiyEmail()` and never syncs `city_user`; reusing it to mint admins means unverified admin accounts with no scope. | **High** |
| **V3** | **Mass-assignment / IDOR on `type_id`, space-event `city_id`, and user `cities`.** `SpaceStoreRequest` validates `type_id` against *all* types (not the city-admin subset); `EventService` mass-assigns `city_id` from raw input with no rule; `CityUserController`/`UserController` sync arbitrary `cities` with no role/scope pairing. | **High** |
| **V4** | **Pitch-save data-integrity bug.** The edit screen does a two-step, non-transactional save; step 1 (`ProductController@update`) persists the pitch, step 2 (`ProductEventController@update`) fails validation (`pitch_started_at/ended_at/timezone` required) → "Oops" while a **partial pitch with no city/country** remains. | **High** |
| **V5** | **No "one City Admin per city" enforcement + concurrent-approval race.** `city_user` is only unique on `(user_id, city_id)`; nothing prevents two admins for the same city, and two concurrent approvals would both succeed. | **Medium** |

(See also the orphaned `CityAdministratorSeeder` / hardcoded role strings (R2, R4), unregistered `SpaceEventPolicy`, and media-upload risks in the companion doc.)

### 1.4 Recommended sequencing at a glance

```
Phase 0  Foundation (role registry + idempotent seeder + UserRoleService)   ← already specified in user-approval-roles-refactor-plan.md PR 1–3
Phase 1  Generalize scope (user↔scope) + formalize City Admin               ← register SpaceEventPolicy, fix dead-code branches, add config role name
Phase 2  Add Special Events Admin role (seeded, unassigned first)
Phase 3  Promote Pitch location to FKs (city_id + location_id) + server-side scope (fixes V1, V3)
Phase 4  Fix pitch-save bug: single transactional save + JSON response (fixes V4)
Phase 5  Application→Approval workflow for admin roles (+ email verification, fixes V2, V5) + auto-provision city/SE-admin page
Phase 6  Pitch field UX changes + 14-day rule for Special Events
Phase 7  Landing navigation drill-down + Location-type removal/redesign + map pins + search fix
```
Full per-phase detail (goal / changes / risk / revertibility / tests) is in the [companion doc §9](./new-requirements-security-scalability-and-phasing.md#9-phased-refactor--implementation-plan).

---

## 2. Current-State Mapping

For each requirement area: the requirement, the real code it touches, and where code **conflicts** with the requirement.

### 2.1 Landing page / navigation

| Requirement | Current code | Conflict / gap |
|---|---|---|
| Submenu under country → select city → list upcoming pitches → list events of a pitch | Header menus are CMS-driven: `menus`/`menu_items` (`app/Models/MenuItem.php`) rendered via `MenuService::getFrontendUserMenus()` (`app/Services/MenuService.php`). Space tree pages list leaf descendants (pitches) via `PageSpaceService::getLeaves()`. Default seeder only adds one item titled "Country" → `frontend.spaces.index` (`modules/Space/Database/Seeders/MenuSeeder.php`). | **No code auto-builds a Country→City submenu of upcoming pitches.** The drill-down is partly possible only by manually building menu items and relying on the Space tree. "City & Pitches", "All Countries", "Netherlands", "Sweden" are **DB values** (menu titles / Space names), not source constants. |
| "404's are thrown and unique ID errors depending on whether pitches/events have been added" | `PageSpaceService::getPageTranslationFromRequest()` resolves a multi-segment URL using **only the last segment**: `PageTranslation::whereSlug($slugs->last())` (`modules/Space/Services/PageSpaceService.php:30-33`). `Frontend\SpaceController::notFoundHandler()` redirects to homepage. New space pages are created with `slug = null, unique_key = null` (`modules/Space/Http/Controllers/SpaceController.php:335-338`). `getSlugs()` falls back to `$localeTranslation->uniqueKey` but the column is `unique_key` with **no `getUniqueKeyAttribute` accessor** (`modules/Space/Entities/PageTranslation.php:44-69`). A self-redirect uses a **positional** route param where the route expects `['slugs' => ...]` (`modules/Space/Http/Controllers/Frontend/SpaceController.php:140-143`). | **Verified likely root causes** of the 404 / unique-ID errors: (a) last-segment-only slug resolution → collisions / wrong page; (b) `null`/`uniqueKey`-typo slug segments when a pitch/city page has no published translation; (c) malformed redirect parameter shape. These are real bugs, not just data issues. |
| "Search function does not work on test" ("Search Events" / "No Events Found") | Search lives in the events-calendar page-builder component (`modules/Booking/Resources/assets/js/PageBuilderComponents/EventsCalendar.vue`) calling `api.booking.events-calendar.index` → `EventsCalendarService::getRecords()`. Location options come from `getLocationOptions()`. | **Verified likely causes:** (1) `EventsCalendarService::availableTypes()` returns **only `['booked_event']`** (`modules/Booking/Services/EventsCalendarService.php:56-63`), so `space_event` rows in the `event_calendars` view are never returned. (2) Country filter mismatch: the request validates `country` as **max 2 chars** (`EventsCalendarRequest`) and the frontend sends 2-letter codes, but `cities.country_code` is **3 chars** (e.g. `NLD`) — filters silently match nothing. (3) Default 7-day date window excludes most events. (4) Requires qualifying booked events with non-null location meta. A `fix-event-calendars-view.sql` already exists in the repo, indicating the view has been problematic. |

### 2.2 Account request / creation (City Admin & Special Events Admin)

| Requirement | Current code | Conflict / gap |
|---|---|---|
| Self-service sign-up to apply for City Admin (specify city + logos/banners/text/descriptions); super admin approves/rejects; on approval auto-create a city-admin page "like street performers" | The street-performer template is the FormBuilder "Automate User Creation" flow: `modules/FormBuilder/Http/Controllers/AutomateUserCreationController.php` + `AutomateUserCreationService.php`. Approval gate = `FormEntryPolicy::automateUserCreation`. Role chosen via `FormMappingRule` (`group='role'`). | **No approval *state machine*** (R1). The form path **does not verify email** (R5) and **does not sync `city_user`** scope. There is **no concept of a "city admin page"** auto-provisioning from supplied branding; `SpaceService::ensureCitySpacesExist()` only creates a bare City-type Space from `adminCities`, with no logos/banners/text. |
| "There can only be one city admin per city" | `city_user` pivot unique only on `(user_id, city_id)` (`database/migrations/2025_12_03_021806_create_city_user_table.php:28`). | **Not enforced.** Multiple users can administer the same city. No DB or app-level "one admin per city" constraint. |
| Special Events Admin is a separate role, set up/administered the same way; a city can have **many** special-events admins | No `special_events_admin` role exists. `config/permission.php role_names` lists only admin/performer/super_admin (city_administrator is absent even there). | **New role required.** Cardinality differs from City Admin (many-per-city vs one-per-city) — the scope model must support both. |
| Super admins can manually create both, and can do everything those admins can | `UserController@store` assigns role + syncs `cities`; Super Admin bypasses all gates via `Gate::after` (`app/Providers/AuthServiceProvider.php:68-70`, literal `'Super Administrator'`). | Manual create works for City Admin today (with the city UI in `User/Create.vue`/`Edit.vue` keyed on the role); needs extending to Special Events Admin. "Do everything" already true for Super Admin; **must be made explicitly true for Administrator** too (see §2.7). |

### 2.3 Special Events Admin

| Requirement | Current code | Conflict / gap |
|---|---|---|
| Can set up special-events pitches, **max duration 14 days** | Pitch = Product; "duration" = `bookable_date_range` (max **365**) and `pitch_started_at`/`pitch_ended_at` with only `after_or_equal` validation (`modules/Booking/Http/Requests/ProductEventRequest.php`). | **No 14-day cap anywhere** (verified). Must be added to validation (`ProductEventRequest`/`ProductEventCrudRequest`), availability math (`ProductEventService::maxBookableDate`), and the date pickers. |
| Special events visible all year, bookable only within the 14-day window | `maxBookableDate()` already supports a fixed window via `pitch_ended_at` (`modules/Booking/Services/ProductEventService.php:489-514`). Visibility is governed by pitch `status` + `product.roles` meta. | Need to **decouple "visible" from "bookable"**: special-events pitches must render year-round but only accept bookings inside the allocated ≤14-day window. |
| Connected to a city and a location; different-color map pin | Pitch location is meta `locations` (city string + country_code), no `city_id`/`location_id` FK. Map markers use a **single** Google Maps `Marker` style (`EventsCalendar.vue:546-552`), no type-based color; no Leaflet. | **Both gaps:** no real city/location linkage (V1), and no per-type pin styling. Pin color requires a "special event" flag on the calendar record/view. |
| Locations can be created by Special Events Admin | Location creation = `SpaceController@store` gated by `SpacePolicy::create` (`space.add` OR parentable space OR `city_administrator`). | Need to allow the new role; today only `city_administrator` is whitelisted. |

### 2.4 City Admin

| Requirement | Current code | Conflict / gap |
|---|---|---|
| Split "Booking" top-level menu into "Pitches" and "Bookings" | Admin nav is module-driven; Booking routes live under `/admin/booking` (`modules/Booking/Routes/web.php`). Menu structure assembled in `MenuService`. | Cosmetic/IA change in admin nav generation + module menu options; low risk. |
| "Location should NOT have a type"; new locations only siblings to cities; a city cannot have a sibling city | "Type" = `spaces.type_id` → `global_options` (the Country/City/Pitch/SpecialEvents-Festivals taxonomy). Space is a nested set (`parent_id`, `_lft/_rgt`), `maxParentDepth = 2` (`modules/Space/ModuleService.php:87-89`). `SpaceStoreRequest` validates `parent_id ∈ cityAdminParentOptions` for city admins but **does not** enforce "child of a City node". | **Direct conflict:** the "type" is currently *the* discriminator for Country/City/Pitch. Removing it from "Location" requires either (a) a dedicated Location entity distinct from Country/City/Pitch nodes, or (b) keeping the type internally but hiding/locking it for locations. "Sibling of city / no sibling city" rules are **not enforced** today. |
| Locations created by City Admin, attached to a city | `SpaceController@store`: city admin's created spaces are added to their `space_user` pivot; `city_id` is **not in the request rules and never set** by `Space::saveFromInputs()`. | **Bug/gap:** child spaces can have `null city_id`, which silently weakens the `whereIn('city_id', ...)` scope filter used in `index`. Location→city linkage must be made explicit and validated. |
| Pitches only attached to cities created by admin/city admin, and **must have a location** | Pitch city = free-text meta string; no `location_id`; `ProductForm`'s `FieldsetLocation` does **not** restrict cities (unlike `SpaceForm` which passes `:restricted-cities`). | **Not enforced** (V1). "Must have a location" is not validated; location can be empty. |

### 2.5 Performer

| Requirement | Current code | Conflict / gap |
|---|---|---|
| A performer books an available timeslot from a pitch; the booked timeslot becomes an event | `Frontend\OrderController@bookEvent` → `OrderService::bookEvent()` creates `orders` + `order_lines` (type `EVENT`) + an `events` row (`modules/Ecommerce/Services/OrderService.php:393-411`). Availability computed by `EventService::availableTimes()`. Frontend visibility gated by `product.roles` meta + `ProductPolicy::showFrontendProductEvent`. | **Matches reality.** Note: a performer books against a **published `ProductEvent` window**, then picks a date/time within it. The booking routes carry only `auth` middleware; authorization is enforced in `EventBookRequest::authorize()` (published product + published ProductEvent). Cancellation behavior (Open Question 4) is undefined in requirements. |

### 2.6 Creating a pitch (the detailed field list)

| Requirement clue | Current code | Conflict / gap |
|---|---|---|
| Gallery disabled; logo & cover disabled | Pitch form has **Gallery + Upload only** (`ProductForm.vue:189-206`); there is **no separate logo/cover field** on a pitch — "cover" is derived from the first gallery image (`Product::getCoverThumbnailUrl()`). | "Disable gallery/logo/cover" = hide the gallery uploader; there is no logo/cover field to disable (they belong to **Space**, not Product). |
| All options visible without saving first | Create page (`ProductCreate.vue`) submits a **minimal** payload (name/status/description/roles/check-in/gallery/space_id); location/duration/date-range/timezone/schedule appear **only on edit** (`ProductEdit.vue` merges them). | **Direct conflict:** the create form intentionally hides most fields until the product exists. Requires unifying create+edit into one full form. |
| City options limited to cities created by admin/city/special-events admin (type of admin defines access) | `ProductForm` → `FieldsetLocation` does not pass `restricted-cities`; no server-side city scope on pitch save. | **Not implemented** (V1). Must restrict **server-side** by the actor's scope, keyed on admin type. |
| "Duration" → rename "Timeslot duration"; swap places with "Pitch date range" | Labels: `i18n.duration` ("Duration", `ProductForm.vue:93`), `i18n.pitch_date_range` ("Pitch Date Range", `:143`). | Label + ordering change; backend meta keys (`duration`, `pitch_started_at/ended_at`) can stay. |
| "Events are timeslots booked by performers" | Confirmed — `events` table rows. | Terminology already consistent; surface in copy. |
| **Timezone listed twice (bug)** | **Confirmed.** `pitch_timezone` (stored in product meta) at `ProductForm.vue:153-159` **and** `timezone` (stored on the `Schedule`) at `ProductForm.vue:173-180`. | Two genuinely different fields with near-identical labels. Needs consolidation or clearer labeling (see FR-PITCH-9). |
| "Weekly hours" → "Weekly days and hours"; copy → "Use this option to manually override day(s)/hours within the pitch date range" | `i18n.weekly_hours` ("Weekly Hours", `ProductForm.vue:183`); tip text `i18n.tips.weekly_hours`. | Copy-only change in `resources/lang/...`. |
| Allow saving a pitch with **no events created** | Pitch (Product) and ProductEvent are already separate; a pitch can exist without ProductEvents. But frontend booking requires ≥1 **published** ProductEvent (`ProductPolicy::showFrontendProductEvent`). | Saving is already possible; the requirement is mostly about the **save bug** (next row) and making the empty state explicit/valid. |
| **BUG: cannot save pitch ("Oops"), but pitch is created on returning to overview, missing country/city** | **Confirmed root cause:** `ProductEdit.vue:540-593` does a **two-step, non-transactional** save: step 1 `POST` `ProductController@update` (succeeds → pitch row saved), step 2 `PUT` `ProductEventController@update` (location + schedule). Step 2 fails when `pitch_started_at/ended_at/pitch_timezone` are required-but-empty (`ProductEventRequest.php:19-21`) → `oopsAlert()`. Location (city/country) is written **only in step 2** (`ProductEventController.php:51-94`) so it is lost. `ProductController@update` never touches location; create only sets location when `space_id` is provided. `city_id` is **never persisted** (only the city *string*). | **High-severity data-integrity bug (V4).** Needs a single transactional save endpoint returning JSON. |
| "Only street performers can create events?" (open question) | `ProductEvent` (admin window) creation is gated by `can:update,product` (admin/city-admin/manager). Booked `events` are created by whoever can see a published pitch+ProductEvent for their role. There is **no `street_performer` role** (closest is `Performer`). | Carry to Open Questions; clarify whether "events" here means admin ProductEvents or performer bookings. |

### 2.7 Admin

| Requirement | Current code | Conflict / gap |
|---|---|---|
| Admin should do everything a city admin / special events admin can | `Administrator` has all wildcard `*.*` + `system.*` (`UserAndPermissionSeeder`), but city scope is enforced via `adminCities` **pivot**, and policies like `ProductPolicy::canManageProductSpace`/`SpaceEventPolicy` check `isCityAdmin($cityId)` / `adminCities`. An Administrator with no `city_user` rows would **fail** the scoped checks. | **Conflict:** capability ≠ scope. Either Administrator must bypass scope (treated as global), or be auto-scoped to all cities. Must be made explicit in policies (see §4 and FR-ADMIN-1). |

---

## 3. Formal Functional Requirements Specification (FRS)

Numbered, grouped by area. "Current" vs "Target" noted; affected components cited. Client wording preserved in quotes.

### 3.1 Landing / Navigation (FR-NAV)

- **FR-NAV-1 — Country→City→Pitch→Events drill-down.** The public site SHALL provide navigation: select a **country**, then a **city** (submenu under country), which lists **upcoming pitches** in that city; selecting a pitch lists its **events**.
  - *Components:* `MenuService`, `menus`/`menu_items`, `Frontend\SpaceController`, `PageSpaceService::getLeaves()`, theme navbar (`themes/biz/views/components/headers/navbar-layout-two.blade.php`), events-calendar component.
  - *Current:* only a manual "Country" link + Space-tree leaf listing; no city submenu of upcoming pitches. *Target:* a generated, data-driven drill-down (see Open Question 7 for the canonical model it binds to).
- **FR-NAV-2 — No broken levels.** Navigating any menu level SHALL NOT throw 404s or "unique ID" errors regardless of whether pitches/events exist.
  - *Components:* `PageSpaceService::getPageTranslationFromRequest()` (full-path slug resolution, not last-segment), `PageTranslation::getSlugs()` (`unique_key` accessor / null-segment handling), `Frontend\SpaceController` redirect param shape.
  - *Current:* last-segment slug lookup + `uniqueKey` typo + malformed redirect cause 404s. *Target:* resolve by full ancestor path; never emit null/`unique_key` segments; empty levels render an empty state.
- **FR-NAV-3 — Working event search.** The "Search Events" feature SHALL return matching events for the selected criteria (country/city/date range), and show the empty state ("No Events Found") only when there genuinely are none.
  - *Components:* `EventsCalendar.vue`, `EventsCalendarController`, `EventsCalendarService` (`availableTypes()`, `getLocationOptions()`, country-code length), `event_calendars` view.
  - *Current:* booked-events-only, 2-vs-3-char country mismatch, 7-day default window → empty results. *Target:* include the requested event types, normalize country codes, sensible default window.
- **FR-NAV-4 — Color-coded map pins.** Special-events pitches SHALL render with a **different-color pin** from normal pitches on maps; when browsing a city, **both** special-events and normal pitches SHALL be visible.
  - *Components:* `EventsCalendar.vue` marker creation, `event_calendars` view (add a type/flag column), `EventsCalendarService`.
  - *Current:* single marker style; space-events excluded. *Target:* per-type marker styling driven by a `is_special_event`/category flag.

### 3.2 Account / Approval (FR-ACCT)

- **FR-ACCT-1 — City Admin application.** A user SHALL be able to apply to become a **City Admin** via a sign-up form, specifying a target **city** and branding (**logos, banners, text, descriptions**).
  - *Components:* FormBuilder form OR a dedicated application entity (see Open Question 3); media upload; `cities`.
- **FR-ACCT-2 — Special Events Admin application.** Same as FR-ACCT-1 but for the **Special Events Admin** role; "set up and administered the same way".
- **FR-ACCT-3 — Approve / Reject.** A Super Admin SHALL be able to **approve or reject** an application. On **approval**, the system SHALL (a) create/promote the user with the correct role, (b) assign the city scope, (c) **auto-provision the admin's city page** using the supplied branding, and (d) ensure the account is email-verified.
  - *Components:* approval action (extending/replacing `AutomateUserCreationController` path), `UserRoleService` (from the role refactor), generalized scope assignment, `SpaceService::ensureCitySpacesExist()` extended with branding, email verification.
  - *Current:* the form path mints **unverified, unscoped** users (R5, V2). *Target:* verified + scoped + branded page.
- **FR-ACCT-4 — One City Admin per city.** The system SHALL enforce that a city has **at most one** City Admin (covering create, approval, transfer — see Open Question 3), including under concurrent approvals.
  - *Components:* DB constraint / unique index on scope table for the city-admin role + transactional approval. *Current:* not enforced (V5).
- **FR-ACCT-5 — Many Special Events Admins per city.** The system SHALL allow **multiple** Special Events Admins for the same city.
- **FR-ACCT-6 — Manual creation by Super Admin.** Super Admins SHALL be able to manually create both City Admins and Special Events Admins (role + city scope) without going through the application flow.
  - *Components:* `UserController@store`, `User/Create.vue`/`Edit.vue` city UI (extend to the new role), `UserStoreRequest`.

### 3.3 Special Events Admin (FR-SE)

- **FR-SE-1 — Create special-events pitches.** A Special Events Admin SHALL be able to create pitches flagged as **special events**, scoped to **their** cities/locations.
- **FR-SE-2 — 14-day max duration.** A special-events pitch's bookable window SHALL NOT exceed **14 days**.
  - *Components:* `ProductEventRequest`/`ProductEventCrudRequest` validation, `ProductEventService::maxBookableDate()`, date pickers. *Current:* no cap (max 365).
- **FR-SE-3 — Visible all year, bookable only in window.** Special-events pitches SHALL be **visible year-round** but **bookable only within** the allocated ≤14-day window.
  - *Components:* pitch visibility (status/roles) decoupled from `min/maxBookableDate` availability math.
- **FR-SE-4 — City + location linkage + distinct pin.** A special-events pitch SHALL be connected to a **city** and a **location**, and marked with a **different-color pin**.
  - *Components:* pitch `city_id`/`location_id` FKs (FR-PITCH-2), calendar flag, marker styling (FR-NAV-4).
- **FR-SE-5 — Location creation.** A Special Events Admin SHALL be able to create **locations** (within their scope).
  - *Components:* `SpacePolicy::create` whitelist extension.

### 3.4 City Admin (FR-CA)

- **FR-CA-1 — Menu split.** The admin "Booking" top-level menu SHALL be split into top-level **"Pitches"** and **"Bookings"**.
- **FR-CA-2 — Location has no type.** "Location" SHALL NOT expose a **type** dropdown. New locations SHALL only be created as **siblings to cities** (i.e., children of a city node), and a **city SHALL NOT have a sibling city**.
  - *Components:* `SpaceForm.vue` (remove/lock type), `SpaceStoreRequest` (enforce parent = city node; forbid city-as-sibling-of-city), hierarchy model (Open Question 6).
  - *Current:* `type_id` is the Country/City/Pitch discriminator; sibling rules unenforced.
- **FR-CA-3 — Location creation by City Admin, attached to a city.** A City Admin SHALL create **locations attached to a city** (within their `adminCities`); `city_id` SHALL be persisted and validated.
  - *Current:* `city_id` never set on child spaces (gap).
- **FR-CA-4 — Pitch scope + mandatory location.** Pitches SHALL only be attached to cities created by an admin/city admin, **scoped to the actor**, and SHALL have an associated **location** (required).
  - *Components:* server-side city/location scope on pitch save (V1), `ProductEventRequest` `location` required.

### 3.5 Performer (FR-PERF)

- **FR-PERF-1 — Book a timeslot → event.** A Performer SHALL book an **available timeslot** from a pitch; the booked timeslot SHALL become an **event** (`events` row + order).
  - *Target (FR-BOOK):* timeslots come from the **pitch schedule** only — no admin `ProductEvent` window required. See [`plan-events-overhaul-pitch-timeslots.md`](./plan-events-overhaul-pitch-timeslots.md).
- **FR-PERF-2 — Cancellation.** Performer may cancel; cancelled booking frees the slot (OQ4 answered YES). Implemented: `OrderService::cancelBooking()`.

### 3.5b Direct pitch booking (FR-BOOK) — *supersedes ProductEvent dependency*

> Execution plan: [`plan-events-overhaul-pitch-timeslots.md`](./plan-events-overhaul-pitch-timeslots.md) · Target tests: `@group events-overhaul`

- **FR-BOOK-1 — Pitch-only timeslots.** Bookable timeslots SHALL be computed **only** from the pitch `Schedule` within `pitch_started_at`–`pitch_ended_at`. Admin `ProductEvent` entities SHALL be **removed**.
- **FR-BOOK-2 — Performer creates events on book.** Booking SHALL create an `events` row + order without `product_event_id`.
- **FR-BOOK-3 — Performer pitch list.** List SHALL show **published, role-scoped pitches** in date range; fully booked pitches remain visible with booking disabled when no slots remain (D6).
- **FR-BOOK-4 — Public booked calendar.** Public pitch pages SHALL show **upcoming booked events** only (guests; no availability preview — D5).
- **FR-BOOK-5 — No admin Events tab.** Pitch admin UI SHALL NOT expose ProductEvent CRUD.
- **FR-BOOK-6 — Batch book retained.** `bookEventBatch` SHALL remain available for performers (D7).
- **FR-BOOK-7 — Reschedule + cancel retained.** Existing reschedule and cancel flows SHALL remain (D9).

### 3.6 Pitch Creation (FR-PITCH)

- **FR-PITCH-1 — Created by City Admin, Special Events Admin (14-day cap), or Super Admin.** Pitch creation SHALL be available to City Admin, Special Events Admin (subject to FR-SE-2), and Super Admin (and Administrator per FR-ADMIN-1).
- **FR-PITCH-2 — Real city/location linkage.** A pitch SHALL store **`city_id`** and a **location** reference (not a free-text city string), enabling server-side scope and the navigation drill-down.
  - *Current:* location is JSON meta with a city string; `city_id` never saved (V1).
- **FR-PITCH-3 — All options visible without saving first.** The pitch create form SHALL show **all** fields (location, duration, date range, timezone, schedule) before the first save.
  - *Current:* create form is minimal; full fields only on edit.
- **FR-PITCH-4 — City options limited by actor scope.** The city selector SHALL offer **only** cities within the actor's scope (admin type defines access), enforced **server-side**.
- **FR-PITCH-5 — Rename + reorder.** "Duration" SHALL be renamed **"Timeslot duration"** and SHALL **swap position** with **"Pitch date range"**.
- **FR-PITCH-6 — "Weekly hours" copy.** "Weekly hours" SHALL be renamed **"Weekly days and hours"** with help copy: *"Use this option to manually override day(s)/hours within the pitch date range."*
- **FR-PITCH-7 — Save with no events.** Saving a pitch with **no events created** SHALL be allowed and SHALL NOT error.
- **FR-PITCH-8 — Fix the save bug.** Saving a pitch SHALL be **atomic**: either the full pitch (including city/country) is saved or nothing is, with a correct success/error response (no silent partial pitch).
  - *Components:* unify `ProductController@update` + `ProductEventController@update` into one transactional endpoint returning JSON; or wrap the two-step client save in a server transaction.
- **FR-PITCH-9 — Single timezone.** The timezone field SHALL appear **once** (resolve the `pitch_timezone` vs schedule `timezone` duplication).
- **FR-PITCH-10 — Disable gallery/logo/cover.** The gallery uploader SHALL be disabled on the pitch form (logo/cover do not exist on pitches today; confirm intent — Open Question).

### 3.7 Admin (FR-ADMIN)

- **FR-ADMIN-1 — Administrator inherits City/SE-Admin capabilities across all scope.** An `Administrator` SHALL be able to perform every action a City Admin or Special Events Admin can, **for any city** (treated as globally scoped). A **Super Administrator** SHALL inherit all (already via `Gate::after`).
  - *Components:* scoped policies (`ProductPolicy::canManageProductSpace`, `SpaceEventPolicy`, `SpacePolicy`) must treat Administrator/Super Administrator as in-scope for all cities.
  - *Current:* scoped policies key on `adminCities`/`isCityAdmin`, so an Administrator with no `city_user` rows would be denied.

---

## 4. Role Matrix

Legend: **✓** allowed (global) · **✓(scope)** allowed only within the user's assigned cities/locations · **✓(14d)** allowed with the 14-day special-events cap · **✗** denied · **—** n/a.

Capability mapping reconciles with the Spatie permission model (capability) **plus** the scope tables (`city_user` today; generalized user↔scope going forward). Permissions in parentheses are the gating permission(s) verified in code.

| Action (gating permission) | Super Admin | Administrator | City Admin | Special Events Admin | Performer | Author | Guest |
|---|---|---|---|---|---|---|---|
| Approve/reject role applications | ✓ | ✓ *(if delegated; see OQ)* | ✗ | ✗ | ✗ | ✗ | ✗ |
| Create City Admin / SE Admin (manual) (`user.add`) | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Manage roles & permissions (`RolePolicy` → Super only) | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Create city (`cities`) | ✓ | ✓ | ✗ *(assigned, not created — see OQ3)* | ✗ | ✗ | ✗ | ✗ |
| Create location (`space.add` / role whitelist) | ✓ | ✓ | ✓(scope) | ✓(scope) | ✗ | ✗ | ✗ |
| Edit/delete location (`space.edit` / `SpacePolicy::manage`) | ✓ | ✓ | ✓(scope)¹ | ✓(scope)¹ | ✗ | ✗ | ✗ |
| Create normal pitch (`product.add` / role) | ✓ | ✓ | ✓(scope) | ✓(scope)² | ✗ | ✗ | ✗ |
| Create special-events pitch | ✓ | ✓ | ✓(scope)³ | ✓(scope)(14d) | ✗ | ✗ | ✗ |
| Edit pitch (`product.edit` / `canManageProductSpace`) | ✓ | ✓ | ✓(scope) | ✓(scope) | ✗ | ✗ | ✗ |
| Edit pitch created by **another** city admin | ✓ | ✓ | OQ2 | OQ1/OQ2 | ✗ | ✗ | ✗ |
| Create admin "Events" (ProductEvent windows) (`can:update,product`) | ✓ | ✓ | ✓(scope) | ✓(scope) | ✗ | ✗ | ✗ |
| Book a timeslot → event (`EventBookRequest`/`product.roles`) | ✓⁴ | ✓⁴ | ✓⁴ | ✓⁴ | ✓ | ✗ | ✗ |
| Cancel a booking (OQ4) | ✓ | ✓ | ✓(scope) | ✓(scope) | OQ4 | ✗ | ✗ |
| View pitch listing (`product.browse` / role) | ✓ (all) | ✓ (all) | ✓(scope) | ✓(scope) | ✗ | ✗ | ✗ |
| Manage own cities scope (`city_user`) | ✓ | ✓ | ✗ (assigned by admin) | ✗ (assigned by admin) | ✗ | ✗ | ✗ |
| Admin dashboard access (`system.dashboard`) | ✓ | ✓ | ✓ | ✓ (decision needed) | ✗ | ✓ | ✗ |
| Public profile page (`public_page.profile`) | — | — | — | — | ✓ | ✗ | ✗ |
| Browse public landing / search events | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |

Footnotes / reconciliation notes:
1. **¹ Scope gap today:** `SpacePolicy::manage()` checks the `space_user` pivot (created-by-self) + ancestor ownership, **not** `adminCities`. So a city admin currently can edit only spaces they personally created, not all spaces in their city. Target: scope by city for all admins of that city (and resolve OQ2).
2. **² OQ1:** Whether a Special Events Admin may create/edit **normal** (non-special) pitches is unresolved.
3. **³ OQ5:** Whether a City Admin creates special-events pitches (and overlap rules) is unresolved.
4. **⁴** Higher roles *can* book only if they also satisfy the pitch's `product.roles` visibility meta and `EventBookRequest::authorize()`. In practice booking is a Performer action; admins booking is an edge case.
5. **Administrator scope:** Per FR-ADMIN-1, Administrator and Super Administrator MUST be treated as **in-scope for all cities** in every scoped policy. Today only Super Administrator is guaranteed (via `Gate::after`); Administrator passing capability checks can still fail `adminCities`-based scope checks — this must be fixed.
6. **Capability vs scope (critical):** every scoped row needs **two** grants — a Spatie permission (capability) *and* a scope-table entry (which cities). The new Special Events Admin needs its own scope rows; reusing `city_user` (City-Admin-only today) requires generalization (see ERM §5 and companion §8).

---

## 5. Entity Relationship Model (ERM)

### 5.1 Target conceptual model

The requirements imply a single canonical hierarchy:

```
Country (1) ──< City (N)
City (1) ──< Location (N)            "locations are siblings of cities" → location is a child of a city; a city has no sibling city
Location (1) ──< Pitch (N)           "pitch must have a location"
Pitch (1) ──< Timeslot (computed)    from Schedule → ScheduleRule → ScheduleRuleTime
Timeslot (booked) ── becomes ──> Event (1 per booking)  → Order + OrderLine(EVENT)

City (1) ──< City-Admin (0..1)               "one city admin per city"
City (1) ──< Special-Events-Admin (0..N)     "many SE admins per city"
SpecialEventPitch (N) >── City (1), Location (1)   visible all year, bookable ≤14 days, distinct pin
ApplicationRecord (N) >── User, City, Role        with branding assets (logos/banners/text)
```

### 5.2 Entity → current storage mapping + new artifacts needed

| Conceptual entity | Existing table/model | Cardinality / key rules | NEW work required |
|---|---|---|---|
| **Country** | `countries` (`App\Models\Country`, alpha2/alpha3) **and** Space-type "Country" nodes | Country 1—N City | **Reconcile** the two; pick `countries` as canonical, drop/derive the Space "Country" nodes (Open Question 6). Normalize `country_code` length (2 vs 3 — see V-search). |
| **City** | `cities` (`App\Models\City`) + `city_user` scope pivot; **also** Space-type "City" nodes (`spaces.type_id`, `spaces.city_id`) | City N—1 Country; **no sibling city**; City 1—0..1 CityAdmin; City 1—N SEAdmin | **Reconcile** `cities` vs City-Space. Make `cities` canonical; treat the City-Space as a generated presentation node or drop it. Add `country_id`/normalized `country_code`. |
| **Location** | `Space` (`spaces`, nested set) with `type_id` → `global_options` | Location N—1 City (child of city node); **no `type`** | Either (a) **new `locations` table** (FK `city_id`, no `type`), or (b) repurpose `Space` with `type_id` removed/locked and a forced "child-of-city" rule. Persist & validate `city_id` (currently never set). |
| **Pitch** | Lunar `Product` (`product_type='Event'`) + meta `locations` (city *string*) + morphed `Schedule` | Pitch N—1 Location (**required**), N—1 City; optional special-event flag | **Add FKs**: `city_id`, `location_id` (or a `pitch_location`/`product_location` link), and an `is_special_event` flag (+ optional `special_event_window`). Migrate existing meta-`locations` → FKs (forward data migration). |
| **Timeslot** | *No table* — computed from `Schedule`/`ScheduleRule`/`ScheduleRuleTime` minus booked `events` | Derived per pitch | No new table; add **14-day window** constraint to validation + `maxBookableDate()`. |
| **Event** (booked) | `events` (`Modules\Booking\Entities\Event`) + `orders` + `order_lines(EVENT)` | Event N—1 Pitch (via schedule), N—1 Performer | No structural change; preserve. |
| **Admin "Event" window** | `ProductEvent` (`product_events`) — admin bookable campaign | ProductEvent N—1 Pitch | Keep; clarify naming in UI (avoid clash with booked Event). |
| **City-Admin ↔ City scope** | `city_user` (unique `(user_id, city_id)`) | **one CityAdmin per city** | Add a **per-city uniqueness** constraint for the city-admin role (partial unique index or app-level transactional guard). Generalize to a role-aware scope (next row). |
| **SE-Admin ↔ City scope** | *None today* | **many SEAdmins per city** | Either reuse `city_user` (loses role distinction → ambiguous) or introduce a **generalized `user_scope`** table `(user_id, role_id|role, scope_type, scope_id)`. Recommended: generalized scope (companion §8). |
| **Application / Approval record** | *None* (today a `FormEntry` + implicit `automate_user_creation_at`) | Application N—1 User?, N—1 City, N—1 Role; status pending/approved/rejected; holds branding | **New `role_applications`** table (status enum, requested role, city_id, decided_by, decided_at) + media relations for **logos/banners/text/descriptions**. |
| **City-admin "page" / branding** | `Space` (City node) + `space_pages`/`space_page_translations`; branding lives on Space (`cover`, media) | Page 1—1 City(-Space) | Extend the auto-provision (`SpaceService::ensureCitySpacesExist()`) to **populate branding from the application** (logos/banners/text). Define where banners/text live (Space media + page translations). |
| **User ↔ Role** | `model_has_roles` (Spatie) | User N—N Role | No structural change; add `special_events_admin` + formalize `city_administrator` in the registry. |

### 5.3 Proposed new tables/columns (additive, forward-only)

- `role_applications` — `id, user_id (nullable, applicant), requested_role, city_id, status (pending|approved|rejected), branding (json or media relations), reviewed_by, reviewed_at, reject_reason, timestamps`.
- `user_scope` (generalized) — `id, user_id, role, scope_type ('city'|...), scope_id, unique(user_id, role, scope_type, scope_id)` + a **partial unique index** `(role, scope_type, scope_id) WHERE role = 'city_administrator'` to enforce one-city-admin-per-city. *(Alternative: keep `city_user`, add a separate `special_events_admin_city` pivot. Generalization preferred — companion §8.)*
- `products` (pitch) additive columns/links — `city_id` (FK `cities`), `location_id` (FK to Location/Space), `is_special_event` (bool), `special_event_started_at`/`ended_at` (or reuse `pitch_*` meta promoted to columns). Data migration from meta `locations`.
- `event_calendars` view — add a `is_special_event` / `category` column and **include `space_event`/special types** so the map can color pins and search returns them.
- `cities` — add `country_id` (FK `countries`) and normalize `country_code` to a single canonical length.

> All migrations additive and forward-only; **do not** drop `city_user`, `spaces.type_id`, or meta `locations` in the same release that introduces their replacements (keep dual-read until all consumers migrate). This mirrors the safety rules in [`user-approval-roles-refactor-plan.md` §5](./user-approval-roles-refactor-plan.md).

---

## 6. User Stories with Acceptance Criteria

Given/When/Then, including negative/edge cases.

### 6.1 City-Admin application + approval

> **As** a prospective city organizer, **I want** to apply to administer a city with my branding, **so that** an approved city page is created for me automatically.

- **AC1 (happy path):** *Given* a city with no existing City Admin, *when* I submit the application (city + logos/banners/text/descriptions) and a Super Admin approves it, *then* my account is granted the City Admin role, scoped to that city, **email-verified**, and a city page is created/populated with my branding.
- **AC2 (reject):** *Given* a pending application, *when* a Super Admin rejects it, *then* no role/scope is granted, the applicant is notified, and the application is marked `rejected` with a reason.
- **AC3 (negative — city already has an admin):** *Given* a city that **already has** a City Admin, *when* an application for that city is approved, *then* the system MUST NOT silently create a second city admin — it MUST follow the resolved policy (reject / transfer / replace — **Open Question 3**). Until resolved, the system MUST block and surface a clear error.
- **AC4 (negative — concurrent approvals):** *Given* two pending applications for the same city approved near-simultaneously, *when* both are processed, *then* at most one succeeds (transactional + unique constraint); the other fails gracefully (V5).
- **AC5 (security):** *Given* the approval auto-creates a privileged account, *then* the new account MUST be email-verified (or sent verification) and MUST NOT be created for an email belonging to a protected admin/super-admin (R5, V2).

### 6.2 Special-Events-Admin application + approval

> **As** a festival organizer, **I want** to apply to be a Special Events Admin for a city.

- **AC1:** *Given* approval, *then* I get the `special_events_admin` role scoped to the city; **multiple** SE Admins per city are allowed (FR-ACCT-5).
- **AC2 (edge):** *Given* a city already has SE Admins, *when* another is approved, *then* it succeeds (no per-city uniqueness for this role).
- **AC3:** Same email-verification and protected-email guarantees as 6.1 AC5.

### 6.3 Creating a pitch (with field changes + 14-day rule)

> **As** a City/Special-Events/Super Admin, **I want** to create a pitch with all fields visible and have it save reliably.

- **AC1 (all fields visible):** *Given* the create form, *then* location, **timeslot duration**, **pitch date range** (swapped order), single timezone, and **"Weekly days and hours"** are all visible before first save (FR-PITCH-3/5/6/9).
- **AC2 (scoped cities):** *Given* I am a City/SE Admin, *then* the city selector lists **only** my scoped cities, and the server **rejects** any out-of-scope `city_id` (FR-PITCH-4, V1).
- **AC3 (mandatory location):** *When* I save without selecting a location, *then* validation fails with a clear message (FR-CA-4).
- **AC4 (atomic save):** *When* I save a valid pitch, *then* the pitch **and** its city/country/location persist together; *when* any part fails, *then* **nothing** is persisted and I see a specific error (no silent partial pitch) (FR-PITCH-8, V4).
- **AC5 (save with no events):** *Given* a pitch with **no** ProductEvent windows, *when* I save, *then* it succeeds (FR-PITCH-7).
- **AC6 (14-day cap, special events):** *Given* I am a Special Events Admin (or creating a special-events pitch), *when* I set a bookable window > 14 days, *then* validation fails; ≤14 days succeeds (FR-SE-2). *Edge:* exactly 14 days is allowed; 0/negative ranges rejected.
- **AC7 (year-round visibility, windowed bookability):** *Given* a special-events pitch, *then* it is visible year-round but only accepts bookings within its ≤14-day window; outside the window the timeslots are not bookable (FR-SE-3).

### 6.4 Performer booking a timeslot

> **As** a Performer, **I want** to book an available timeslot.

- **AC1:** *Given* a published pitch with a published event window and an available timeslot, *when* I book, *then* an `events` row + order are created and I receive confirmation (matches current `OrderService::bookEvent`).
- **AC2 (negative — taken slot):** *When* I book a slot already taken (status UPCOMING), *then* the booking is rejected by availability validation.
- **AC3 (cancellation — TBD):** *When* I cancel a booking, *then* behavior follows the resolved policy (event disappears vs returns to available) — **Open Question 4**.

### 6.5 Landing-page drill-down navigation

> **As** a visitor, **I want** to drill Country → City → Pitch → Events.

- **AC1:** *Given* a country with cities, *when* I open the country submenu, *then* I see its cities; selecting a city lists **upcoming pitches**; selecting a pitch lists its **events**.
- **AC2 (negative — empty levels):** *Given* a city/pitch with **no** pitches/events, *when* I navigate to it, *then* I see an **empty state**, NOT a 404 or unique-ID error (FR-NAV-2).
- **AC3 (search):** *Given* the events search, *when* I search by city/country/date, *then* matching events (normal **and** special) are returned; the empty state shows only when there are truly none (FR-NAV-3).
- **AC4 (map):** *Given* the map, *then* special-events pitches show a distinct pin color and both types are visible when browsing a city (FR-NAV-4).

### 6.6 Location management without type

> **As** a City/SE Admin, **I want** to manage locations under my city.

- **AC1:** *Given* the location form, *then* there is **no type dropdown** (FR-CA-2).
- **AC2:** *When* I create a location, *then* it is created as a **child of a city** I administer, with `city_id` persisted (FR-CA-3).
- **AC3 (negative — sibling city):** *When* a create/move would produce a **city sibling of a city** (or a location not under a city), *then* it is rejected (FR-CA-2).
- **AC4 (security):** *When* I submit an out-of-scope `parent_id`/`city_id`/`type_id`, *then* the server rejects it (V3) — not just hidden in the UI.

---

## 7. Open Questions / Assumptions

These carry the client-stated ambiguities **plus** new ones surfaced by the code review. **Nothing here is silently assumed**; where a position is needed to plan, it is labeled **ASSUMPTION (needs confirmation)** with the alternative.

### 7.1 Client-stated ambiguities (carried verbatim)

1. **Can a Special Events Admin edit normal pitches?** (Affects FR-SE-1, role matrix row "Create normal pitch". *No code basis either way.*)
2. **Can a City Admin edit pitches created by another City Admin (esp. on ownership change)?** (Today `SpacePolicy::manage` keys on the `space_user` pivot / ancestor ownership, so cross-admin editing is currently *blocked* unless ownership is shared. Decision needed for city-wide vs creator-only ownership.)
3. **What happens if a City Admin application is approved for a city that already has one — reject / transfer / replace?** (Blocks FR-ACCT-4 enforcement design.)
4. **Can a performer cancel a booking? If yes, does the event disappear or return to an available timeslot?** (`EventCanceled` event + `CancelUpcomingOrOngoingBookings` listener exist; the *semantic* (free the slot vs remove) is unconfirmed.)
5. **Can Special Events pitches overlap with normal pitches?** (Affects availability math and map.)
6. **Exact hierarchy: Country → City → Location → Pitch → Timeslot → Event?** (Critical: the code has **two** parallel hierarchies — `cities`/`city_user` vs the nested-set Space tree. The whole ERM hinges on choosing one canonical model.)
7. **Desired landing navigation Country → City → Pitch → Events — to be documented explicitly.** (Documented in FR-NAV-1; confirm the exact level labels and whether "City & Pitches" is a fixed menu label.)

### 7.2 New questions surfaced by code review

8. **"Location" model decision:** introduce a dedicated `locations` table, or keep `Space` with the `type` hidden/locked? (Removing `type` outright breaks how Country/City/Pitch nodes are distinguished today.)
9. **Reconcile dual City/Country models:** is `App\Models\City`/`countries` the canonical source, with Space "City"/"Country" nodes derived (or removed)? (Tied to OQ6.)
10. **Scope model:** generalize `city_user` into a role-aware `user_scope`, or add a parallel `special_events_admin_city` pivot? (Companion §8 recommends generalization.)
11. **Pitch logo/cover:** the requirement says "pitch logos and cover are disabled", but pitches have **no** logo/cover fields today (only a gallery; cover is derived from gallery). Confirm whether this refers to the Space/city-page branding instead.
12. **"Only street performers can create events?":** does "events" mean admin **ProductEvent windows** or performer **bookings**? There is no `street_performer` role (only `Performer`).
13. **Should `Administrator` (not just Super Admin) be globally scoped** for all city/SE-admin actions (FR-ADMIN-1), and may Administrators **approve** applications, or is approval Super-Admin-only?
14. **Does the City Admin / SE Admin need `system.dashboard`** (full admin panel) or a restricted panel? (Today's seeder grants it, which makes the `MenuService`/`LoginResponse` "city-admin-only menu / redirect to spaces" branches **dead code** — intent must be confirmed.)

### 7.3 Planning assumptions (explicitly flagged)

- **ASSUMPTION (needs confirmation):** The canonical hierarchy is **`countries` → `cities` → `locations` → pitches**, with the Space nested-set tree retained only as a presentation/SEO layer. *Alternative:* make the Space tree canonical and demote `cities` to a lookup. *(Drives ERM §5.)*
- **ASSUMPTION (needs confirmation):** Scope is generalized into a single `user_scope` table; `city_user` is migrated forward and kept read-only during transition. *Alternative:* a second dedicated pivot for SE Admins.
- **ASSUMPTION (needs confirmation):** The role/registry foundation (PR 1–3 of [`user-approval-roles-refactor-plan.md`](./user-approval-roles-refactor-plan.md)) ships **before** the two new roles, and `special_events_admin` uses a snake_case canonical name consistent with `city_administrator`.
- **ASSUMPTION (needs confirmation):** "Approve and auto-create a city page" reuses/extends `SpaceService::ensureCitySpacesExist()` plus a new `role_applications` record; branding is stored as Space media + page-translation content.

> See the [companion document](./new-requirements-security-scalability-and-phasing.md) for the Vulnerabilities & Security analysis, Scalability analysis, and the dependency-ordered Phased Implementation Plan.
