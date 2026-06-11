# Daniel's Email — Performer Booking & Pitch Creation Requirements

> **Source:** Daniel (client email snippet, documented 2026-06-05)  
> **Scope:** Two rule sets — **(a)** logged-in Performer behavior, **(b)** pitch creation by City Admin, Special Events Admin, or global admin.  
> **Related specs:** [`new-requirements-frs-and-refactor-plan.md`](./new-requirements-frs-and-refactor-plan.md) (FR-PERF, FR-PITCH, FR-SE), [`00-START-HERE-implementation-guide.md`](./00-START-HERE-implementation-guide.md), [`PROGRESS-LOG.md`](./PROGRESS-LOG.md)

---

## Terminology (read before implementing)

Daniel's email uses **"event"** in two different senses. The codebase uses the same overload:

| Daniel / UI wording | Code entity | Table / model | Who creates it |
|---|---|---|---|
| **Timeslot** (available slot on a pitch) | Computed availability | *(not stored)* | Derived from `Schedule` minus booked rows |
| **"Events" tab on a pitch (admin)** | Admin bookable window | `product_events` | `ProductEvent` | City / SE / global admin |
| **Booked timeslot → "becomes an event"** | Performer booking | `events` (+ `orders`) | Performer via `OrderService::bookEvent()` |

**Performer-facing list (target after FR-BOOK overhaul):** `/booking/products` lists **published pitches** in date range (not admin `ProductEvent` windows). See [`plan-events-overhaul-pitch-timeslots.md`](./plan-events-overhaul-pitch-timeslots.md).

**Current code (pre-T8):** still lists published `ProductEvent` rows — being replaced.

---

## (a) Logged in as Performer

### Daniel's requirement (verbatim)

> A performer books an available timeslot from a pitch. The booked timeslot becomes an event.

### Target behavior

| # | Rule | FRS ID | Acceptance criteria |
|---|---|---|---|
| P1 | A Performer SHALL browse published pitches that include their role (`product.roles` meta) and at least one published admin event window (`ProductEvent`). | FR-PERF-1 (partial) | Published pitch + published `ProductEvent` + Performer in pitch roles |
| P2 | A Performer SHALL pick an **available timeslot** within that window (computed from schedule minus existing bookings). | FR-PERF-1 | `EventService::availableTimes()` |
| P3 | On successful booking, the system SHALL create an **`events` row**, an order, and order line(s). | FR-PERF-1 | `OrderService::bookEvent()` |
| P4 | Cancellation: performer may cancel; cancelled booking frees the slot (client decision OQ4). | FR-PERF-2 | `OrderService::cancelBooking()` — see `T-PERF-CANCEL` in progress log |

### Code touchpoints

- Performer list: `modules/Booking/Http/Controllers/Frontend/ProductController.php` → `ProductEventCrudService::getFrontendRecords()`
- Book flow: `modules/Booking/Http/Controllers/Frontend/OrderController.php` → `OrderService::bookEvent()`
- Authorization: `ProductPolicy::showFrontendProductEvent`, `ProductEventPolicy::showFrontendProductEvent`, `EventBookRequest::authorize()`

### Implementation status

| Item | Status |
|---|---|
| Book timeslot → `events` row | ✅ Matches existing behavior |
| Performer cancellation (OQ4) | ✅ DONE (`T-PERF-CANCEL`) |

---

## (b) When Creating a Pitch

**Actors:** City Admin, Special Events Admin (14-day max on bookable window), Super Admin / Administrator when needed.

### Daniel's requirements (verbatim structure)

#### Who may create

- Pitches are created by **city admin**, **special events admin** (with **14 day max** restriction), or **super admin** if needed.

#### From the pitch form

| # | Daniel's requirement | FRS ID | Notes |
|---|---|---|---|
| C1 | **Gallery is disabled.** | FR-PITCH-10 | Hide gallery uploader on pitch form. Pitches have no separate logo/cover fields today (OQ11 — deferred branding spec). |
| C2 | **All options to fill out should be visible without saving first.** | FR-PITCH-3 | Create form must show location, timeslot duration, pitch date range, timezone, schedule before first save — not a minimal create-then-edit flow. |
| C3 | **City options limited** to cities created by admin / city admin / special events admin. Type of admin defines access to which cities. | FR-PITCH-4, FR-CA-4 | UI **and** server-side scope (`UserScopeService`, `InScopedCityId`). |
| C4 | **Duration** renamed **"Timeslot duration"** and **swap places** with **"Pitch date range".** | FR-PITCH-5 | Label + field order only; backend keys (`duration`, `pitch_started_at` / `pitch_ended_at`) unchanged. |
| C5 | **"Events are timeslots that are booked by performers."** | — (terminology) | Clarifies admin **Events tab** (`ProductEvent`) vs booked **`events`** rows. See terminology table above. |
| C6 | **Timezone is listed twice** (bug). | FR-PITCH-9 | Consolidate `pitch_timezone` (product meta) vs schedule `timezone` into a single field in the UI. |
| C7 | Change **"Weekly hours"** to **"Weekly days and hours".** | FR-PITCH-6 | Label change. |
| C8 | Help copy: **"Use this option to manually override day(s)/hours within the pitch date range."** | FR-PITCH-6 | Tooltip / help text on weekly schedule section. |
| C9 | **Allow saving of a pitch without any events created.** | FR-PITCH-7 | Pitch (`Product`) and admin event windows (`ProductEvent`) are separate; empty Events tab must not block save. Performers still need ≥1 **published** `ProductEvent` to book. |
| C10 | **Only street performers can create events?** (open question in email) | OQ12 | **Answered:** Daniel meant performer **bookings** (timeslots → `events`), not admin `ProductEvent` creation. Admin event windows remain admin-only (`can:update,product`). No `street_performer` role — use `Performer`. |
| C11 | **BUG:** Cannot save pitches — **"Oops something went wrong"** — but pitch appears in overview **missing country and city** even though they were specified. | FR-PITCH-8, V4 | Root cause: two-step non-transactional save (product saved, location step fails). Fix: atomic save via `ProductPitchRequest` / single endpoint. |
| C12 | **Pitch logos and cover are disabled.** | FR-PITCH-10, OQ11 | Gallery disabled on pitch form. Logo/cover on pitches is a **separate future feature** (`req-T-PITCH-BRANDING-logo-cover.md`); not the same as Space/city-page branding. |

#### Special Events Admin — additional rule (from broader spec)

| # | Rule | FRS ID |
|---|---|---|
| C13 | Special-events pitches: bookable window **≤ 14 days**. | FR-SE-2 |
| C14 | Visible year-round; bookable only inside allocated window. | FR-SE-3 |

### Acceptance criteria (from FRS §6.3)

- **AC1:** All fields visible on create before first save (C2, C4, C6, C7, C8).
- **AC2:** City selector lists only scoped cities; server rejects out-of-scope `city_id` (C3).
- **AC3:** Location required on save (FR-CA-4).
- **AC4:** Atomic save — city/country persist together or nothing is saved (C11).
- **AC5:** Save succeeds with zero admin event windows (C9).
- **AC6:** SE Admin pitch date span > 14 days rejected (C13).

### Code touchpoints

| Area | Primary files |
|---|---|
| Pitch form UX | `modules/Booking/Resources/assets/js/Pages/ProductForm.vue`, `ProductCreate.vue`, `ProductEdit.vue` |
| Atomic save | `modules/Booking/Http/Requests/ProductPitchRequest.php`, `ProductController@store` / `@update` |
| Scoped cities | `UserScopeService`, `FieldsetLocation.vue` (`restricted-cities`), `InScopedCityId` validation rule |
| 14-day cap | `MaxInclusiveDaySpan`, `ProductEventService::requiresFourteenDayBookableWindow()` |
| Admin event windows | `ProductEventCrudController`, `ProductEventCrudService` (separate from pitch save) |

### Implementation status

| Requirement | FRS | Status (see [`PROGRESS-LOG.md`](./PROGRESS-LOG.md)) |
|---|---|---|
| C1 Gallery disabled | FR-PITCH-10 | 🟢 T6.1 CODE COMPLETE |
| C2 All fields on create | FR-PITCH-3 | 🟢 T6.1 |
| C3 Scoped city picker + server validation | FR-PITCH-4 | 🟢 T3.2, T6.2 |
| C4 Timeslot duration ↔ pitch date range swap | FR-PITCH-5 | 🟢 T6.1 |
| C6 Single timezone | FR-PITCH-9 | 🟢 T6.1 |
| C7/C8 Weekly days and hours + help copy | FR-PITCH-6 | 🟢 T6.1 (verify copy in staging) |
| C9 Save without admin events | FR-PITCH-7 | 🟢 T4.1 / T6.1 |
| C11 Atomic save (no partial pitch) | FR-PITCH-8 | 🟢 T4.1 |
| C12 Logo/cover disabled | FR-PITCH-10 / OQ11 | Gallery hidden; logo/cover feature spec written, not built |
| C13/C14 14-day SE cap + bookability window | FR-SE-2/3 | 🟢 T6.2 |

**Staging verification still recommended** for T6.1/T6.2/T4.1 before marking ✅ DONE.

---

## Quick reference — Daniel bullet → FRS ID

| Daniel (paraphrased) | FRS |
|---|---|
| Performer books timeslot → event | FR-PERF-1 |
| Created by CA / SE Admin (14d) / Super Admin | FR-PITCH-1, FR-SE-2 |
| Gallery disabled | FR-PITCH-10 |
| All fields visible without saving first | FR-PITCH-3 |
| City options limited by admin scope | FR-PITCH-4 |
| Rename duration; swap with pitch date range | FR-PITCH-5 |
| Events = performer-booked timeslots (terminology) | OQ12 answered |
| Timezone listed twice | FR-PITCH-9 |
| Weekly hours → weekly days and hours + override copy | FR-PITCH-6 |
| Save pitch with no admin events | FR-PITCH-7 |
| Only street performers create events? | OQ12 — bookings only |
| Oops save bug / missing city | FR-PITCH-8 |
| Pitch logos and cover disabled | FR-PITCH-10, OQ11 spec pending build |

---

## Operational note for QA

When testing performer visibility after admins create pitches, confirm **all** of:

1. Pitch status = **Published**
2. Pitch **Roles** includes **Performer**
3. Pitch **location** (city + country) saved
4. At least one row on pitch **Events** tab with status = **Published**

Without (4), the performer Pitches page and city/country filters will show little or no data — by design, not a performer-permission bug.
