# Plan — Events Overhaul: Pitch Timeslots → Performer-Booked Events

> **Status:** Planning (not started)  
> **Created:** 2026-06-05  
> **Driver:** Daniel's model — performers book **timeslots defined on the pitch**; the booked slot **becomes** an `events` row. Admin **`ProductEvent` windows are removed.**

---

## 1. Decisions locked (client / product)

| # | Question | Decision |
|---|---|---|
| D1 | Fate of admin `ProductEvent` (`product_events`) | **Remove entirely.** Pitch schedule + pitch date range are the only bookable source. |
| D2 | Performer list (`/booking/products`) | **Published pitches with ≥1 available timeslot** in the pitch window (role-scoped). |
| D3 | Public nav fourth level (Country → City → Pitch → ?) | **Public calendar of booked/upcoming events** at that pitch. |
| D4 | Existing `ProductEvent` / booking data | **Greenfield** — staging may be wiped/re-seeded; no production migration path required in v1. |
| D5 | Public pitch page (guests) | **Booked/upcoming events only** — no availability preview for guests. |
| D6 | Fully booked pitch in performer list | **Still show** — performer can open pitch but sees fully booked / no book action (differs from D2 filter: list may use availability OR show-all-published; see §7 note). |
| D7 | Batch book (`bookEventBatch`) | **Keep.** |
| D8 | Bookable hours constraint | **Pitch form only** — pitch date range + weekly days/hours + date overrides; no campaign windows. |
| D9 | Reschedule & cancel | **Keep both** (cancel frees slot; reschedule unchanged). |

> **D2 vs D6 reconciliation:** D2 originally said “list pitches with availability.” D6 says show fully booked pitches too. **Implement:** performer list shows **all published, role-scoped pitches in date range**; UI indicates availability (e.g. “Fully booked” badge); Book action disabled when no slots. Optional filter later — not in v1.

### Terminology (target)

| User-facing term | Code | Created by |
|---|---|---|
| **Pitch** | `Product` + pitch `Schedule` | City / SE / global admin |
| **Timeslot** | Computed (`EventService::availableTimes`) | Not stored |
| **Event** (booked) | `events` row (+ order) | **Performer** on book |

**Remove:** admin "Events" tab, `ProductEvent`, `ProductEventCrud*`, `booking.events.*` frontend routes tied to `ProductEvent`.

---

## 2. Current vs target (gap analysis)

### Today

```
Admin: Pitch (Product + Schedule)
         └── ProductEvent window(s)  ← admin creates, publishes
Performer: lists ProductEvents → picks date/time → bookEvent(product, datetime, user, productEvent)
         └── creates events row (product_event_id required)
```

### Target

```
Admin: Pitch (Product + Schedule + pitch_started_at/ended_at + weekly hours/overrides)
Performer: lists Pitches with availability → picks date/time from pitch schedule → bookEvent(product, datetime, user)
         └── creates events row (schedule_id = product.eventSchedule; no product_event_id)
Public: Pitch page → calendar of upcoming booked events at that pitch
```

### Why performers saw empty lists

Under the **old** model, a published pitch with **no published `ProductEvent`** never appeared. Under the **new** model, a published pitch with a valid schedule **will** appear once tooling confirms availability math.

---

## 3. Tooling-first (do this before feature code)

Priority order: **spec → inventory → tests → then refactor.**

### T-TOOL-1 — Spec amendment (1 PR, docs only)

| Deliverable | Action |
|---|---|
| Amend FRS | Update `new-requirements-frs-and-refactor-plan.md`: supersede FR-PERF-1 partial ProductEvent dependency; add **FR-BOOK-1…FR-BOOK-5** (see §6). |
| Source doc | Update `daniel-email-performer-and-pitch-requirements.md` — mark ProductEvent path **deprecated/removed**. |
| This plan | Keep as execution checklist; link from `00-START-HERE-implementation-guide.md`. |
| Entry points | Update `entry-points-map.md` — remove `booking.events.*`, document new public pitch calendar route. |

**Verify:** docs cross-link; no FRS row still requires admin ProductEvent for performer booking.

### T-TOOL-2 — Blast-radius inventory (script + checklist)

Automated inventory (run before every phase):

```bash
# From repo root — save output to migration-preparation/artifacts/product-event-blast-radius.txt
rg -l 'ProductEvent|product_events|product_event_id' --glob '!vendor/**' --glob '!node_modules/**' \
  | sort > migration-preparation/artifacts/product-event-blast-radius.txt
wc -l migration-preparation/artifacts/product-event-blast-radius.txt
```

**Manual checklist categories** (each file must be classified DELETE / REWRITE / KEEP):

| Category | Examples |
|---|---|
| Admin CRUD | `ProductEventCrudController`, `ProductEventList.vue`, `ProductEventFormModal.vue` |
| Frontend book flow | `FrontendEventShow.vue`, `EventController`, `EventBookRequest` |
| Policies / rules | `ProductEventPolicy`, `BookingWithinProductEventRange` |
| DB | `product_events`, `product_event_translations`, `events.product_event_id` |
| Views / calendar | `fix-event-calendars-view.sql`, `EventsCalendarService` |
| Tests | Any test passing `product_event_id` |

**Verify:** inventory file committed; every path has a disposition before Phase B starts.

### T-TOOL-3 — Characterization tests (write first, expect RED on current code)

Add **Feature** tests that describe **target** behavior (will fail until Phases B–D land):

| Test file | Cases |
|---|---|
| `tests/Feature/PitchDirectBookingTest.php` | Performer books pitch without ProductEvent; `events` row created; slot blocked after book |
| `tests/Feature/PitchAvailabilityListingTest.php` | Index lists published pitch in window; fully booked pitch still listed with no book action; excludes draft |
| `tests/Feature/PitchPublicEventCalendarTest.php` | Public pitch page lists upcoming booked events; empty state when none |
| `tests/Feature/ProductEventRemovalTest.php` | Admin pitch edit has **no** Events tab route; `product_events` table absent after migration |

Extend `phase-test-verification-guide.md` § new section **"Events overhaul (FR-BOOK)"**.

**Verify:** `php artisan test --filter=PitchDirectBooking` fails for the *right* reason (ProductEvent still required), not setup errors.

### T-TOOL-4 — Audit skill extension

Extend `.cursor/skills/refactor-progress-audit/SKILL.md` with tasks **T8.1–T8.5** (mirror §7 phases below) so progress checks stay code-verified.

### T-TOOL-5 — Greenfield DB reset script (staging)

Document in this plan (and `PROGRESS-LOG.md`):

```bash
# Staging only — after schema migration drops product_events
./scripts/db-etl.sh safe-migrate   # or migrate:fresh --seed per env policy
# Re-seed pitches via admin UI or ProductSeeder; no ProductEvent seeder
```

**Verify:** fresh migrate + seed boots; performer can book without creating admin events.

---

## 4. Implementation phases (after tooling)

### Phase A — Schema & dead code removal (T8.1)

**Goal:** Database matches target model; admin cannot create ProductEvents.

| Task | Files / actions |
|---|---|
| Migration | Drop `product_events`, `product_event_translations`; drop `events.product_event_id` (greenfield OK) |
| Remove routes | `modules/Booking/Routes/web.php` — admin `product-events` CRUD; frontend `booking.events.*` |
| Remove controllers | `ProductEventCrudController`, `ProductEventController`, `ProductEventScheduleController`, `Frontend\EventController` |
| Remove Vue | `ProductEventList.vue`, `ProductEventFormModal.vue`, `FrontendEventShow.vue`; strip Events tab from `ProductEdit.vue` |
| Remove policy | `ProductEventPolicy`, AuthServiceProvider registration |
| Remove rules | `BookingWithinProductEventRange` |

**Verify:** T-TOOL-2 inventory → 0 unresolved references; app boots; admin pitch edit has no Events tab.

### Phase B — Booking core (T8.2)

**Goal:** `OrderService::bookEvent()` works with **pitch schedule only**.

| Task | Files / actions |
|---|---|
| Signature | `bookEvent(Product, Carbon, User)` — remove `ProductEvent` param |
| Schedule | Always `$product->eventSchedule`; abort if missing |
| Validation | `EventBookRequest` / `EventBookBatchRequest` — drop `product_event_id`; keep `BookingWithinPitchWindow` |
| Event row | `Event::factory()` — `schedule_id` from product; no `product_event_id` |
| Order meta | Remove `product_event_id` from order meta (or leave nullable legacy read) |
| Reschedule | `FrontendOrderReschedule.vue` + reschedule service — drop product event picker |

**Verify:** `PitchDirectBookingTest` green.

### Phase C — Performer pitch listing (T8.3)

**Goal:** `/booking/products` lists **published, role-scoped pitches** in date range (including fully booked).

| Task | Files / actions |
|---|---|
| New service | `PitchAvailabilityService`: `hasAvailableTimeslot(Product): bool`, `availabilityLabel(Product): string` |
| Index query | Replace `ProductEventCrudService::getFrontendRecords()` with `Product` query: `published()`, role filter, within pitch date range |
| UI state | Show pitches with slots → **Book now**; fully booked → badge + disabled book (D6) |
| City/country filters | Derive options from **published pitches** (`city_id` / location meta), not `product_events` |
| UI | `FrontendProductIndex.vue` — columns: Pitch name, dates, city, country, availability |
| Show page | `FrontendProductShow.vue` — remove ProductEvent selector; calendar uses product schedule + pitch window |
| Policies | `ProductPolicy::showFrontendProductEvent` — drop `productEvents()->published()->exists()` |

**Verify:** `PitchAvailabilityListingTest` green; manual: CA publishes pitch only → performer sees it without admin Events tab.

### Phase D — Public pitch event calendar (T8.4)

**Goal:** Nav level 4 — **booked/upcoming events** on public pitch page.

| Task | Files / actions |
|---|---|
| Query | `Event::blockingAvailability()` for pitch's schedule(s) / via order meta `product_id` |
| Route | Public or Space-integrated view on pitch page (align with `PageSpaceService` / pitch leaf template) |
| UI | Read-only calendar/list; no booking without login |
| Search/map | Update `EventsCalendarService` / `event_calendars` view — join booked events to pitch, not `ProductEvent` |

**Verify:** `PitchPublicEventCalendarTest` green; FR-NAV-1 fourth level manual smoke.

### Phase E — Cleanup & docs (T8.5)

| Task | Action |
|---|---|
| Rename confusing services | Consider renaming `ProductEventService` → `PitchBookingService` (optional, separate PR) |
| Docs | `booking-module-execution-flow.md`, `CODEBASE_DOCUMENTATION.md` |
| PROGRESS-LOG | Mark T8.x status |
| Delete | `TIME_RESTRICTIONS_IMPLEMENTATION_SUMMARY.md` references if obsolete |

**Verify:** T-TOOL-2 inventory empty; full test suite green; refactor audit skill reports T8 DONE.

---

## 5. Proposed FRS IDs (for T-TOOL-1)

| ID | Requirement |
|---|---|
| **FR-BOOK-1** | Bookable timeslots SHALL be computed **only** from the pitch `Schedule` within `pitch_started_at`–`pitch_ended_at`. No admin event window entity. |
| **FR-BOOK-2** | A Performer SHALL book a timeslot from a published pitch; booking SHALL create an `events` row + order. |
| **FR-BOOK-3** | Performer pitch list SHALL show **published, role-scoped pitches** within the pitch date range; fully booked pitches remain visible with booking disabled when no slots remain. |
| **FR-BOOK-4** | Public pitch pages SHALL show **upcoming booked events** at that pitch (read-only). |
| **FR-BOOK-5** | Admin pitch UI SHALL **not** expose a separate Events / ProductEvent management tab. |

**Supersedes:** performer dependency on published `ProductEvent` in FR-PERF-1 and FR-PITCH-7 empty-events note (empty admin events becomes the normal case).

---

## 6. Risks & mitigations

| Risk | Mitigation |
|---|---|
| Availability query slow on index | Start with PHP filter + pagination; add SQL/materialized cache in follow-up if needed |
| `event_calendars` view breaks | T-TOOL-3 test + update view in Phase D before deploy |
| Reschedule/check-in paths still reference ProductEvent | T-TOOL-2 inventory + `OrderService` audit |
| Special Events 14-day window | Keep existing `MaxInclusiveDaySpan` on **pitch** dates (T6.2); no ProductEvent cap needed |
| Naming confusion in code | Phase E rename; UI always says "Pitch" / "Event (booking)" |

---

## 7. Open questions — resolved (2026-06-05)

| # | Question | Answer |
|---|---|---|
| OQ-BOOK-1 | Public pitch page for guests? | **Booked/upcoming events only** (no slot availability preview). |
| OQ-BOOK-2 | Fully booked pitch in performer list? | **Show it** — open pitch shows fully booked; disable book when no slots. |
| OQ-BOOK-3 | Keep batch book? | **Yes.** |
| OQ-BOOK-4 | How admins constrain bookable hours? | **Pitch form only** (date range + weekly days/hours + overrides). |
| OQ-BOOK-5 | Keep reschedule & cancel? | **Yes, both.** |

**No remaining blockers for T-TOOL-1 / PR 1** unless Daniel overrides D5–D9.

---

## 8. Suggested PR sequence (one behavior per PR)

1. **Docs + T-TOOL-2 inventory + failing tests** (T-TOOL-1, T-TOOL-3)  
2. **T8.1** Schema drop + remove admin/frontend ProductEvent UI  
3. **T8.2** Booking core without ProductEvent  
4. **T8.3** Performer pitch listing + show page  
5. **T8.4** Public booked-event calendar + search/view fixes  
6. **T8.5** Cleanup + audit skill update  

---

## 9. Manual QA checklist (post Phase E)

- [ ] City Admin: create + publish pitch (no Events tab) with location + schedule  
- [ ] Performer: pitch appears in list without any admin "event" creation  
- [ ] Performer: book timeslot → order + event created; same slot unavailable  
- [ ] Performer: cancel → slot available again (T-PERF-CANCEL)  
- [ ] Guest: public pitch page shows upcoming booked events  
- [ ] SE Admin: 14-day pitch window still enforced  
- [ ] `migrate:fresh --seed` on staging succeeds  

---

*End of plan*
