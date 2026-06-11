# ProductEvent blast-radius disposition (T-TOOL-2)

> Generated: 2026-06-05 · Source: `artifacts/product-event-blast-radius.txt` (54 paths)  
> Legend: **DELETE** remove in T8.1 · **REWRITE** change behavior, keep file · **KEEP** docs only or unrelated rename later

| Path | Disposition | Phase | Notes |
|---|---|---|---|
| `TIME_RESTRICTIONS_IMPLEMENTATION_SUMMARY.md` | KEEP | E | Historical; archive or delete in cleanup |
| `app/Policies/ProductEventPolicy.php` | DELETE | A | Policy removed with entity |
| `app/Providers/AuthServiceProvider.php` | REWRITE | A | Unregister ProductEvent policy |
| `docs/CODEBASE_DOCUMENTATION.md` | REWRITE | E | Update booking flow docs |
| `fix-event-calendars-view.sql` | REWRITE | D | Drop ProductEvent joins |
| `migration-preparation/*.md` | KEEP | 1/E | Spec/plan docs |
| `.cursor/skills/refactor-progress-audit/SKILL.md` | REWRITE | 1/E | T8 task checklist |
| `modules/Booking/Entities/Event.php` | REWRITE | B | `product_event_id` nullable then dropped |
| `modules/Booking/Entities/ProductEvent.php` | DELETE | A | Entity removed |
| `modules/Booking/Entities/ProductEventTranslation.php` | DELETE | A | Entity removed |
| `modules/Booking/Http/Controllers/Frontend/EventController.php` | DELETE | A | Replaced by product show |
| `modules/Booking/Http/Controllers/Frontend/OrderController.php` | REWRITE | B | Drop ProductEvent from bookEvent |
| `modules/Booking/Http/Controllers/Frontend/ProductController.php` | REWRITE | C/D | List pitches; add bookedEvents |
| `modules/Booking/Http/Controllers/OrderController.php` | REWRITE | B | Reschedule without ProductEvent |
| `modules/Booking/Http/Controllers/ProductController.php` | REWRITE | A | Remove Events tab data if any |
| `modules/Booking/Http/Controllers/ProductEventController.php` | DELETE | A | Legacy location step — fold into pitch save only |
| `modules/Booking/Http/Controllers/ProductEventCrudController.php` | DELETE | A | Admin CRUD removed |
| `modules/Booking/Http/Controllers/ProductEventScheduleController.php` | DELETE | A | Admin schedule removed |
| `modules/Booking/Http/Requests/EventBookBatchRequest.php` | REWRITE | B | Remove product_event_id rules |
| `modules/Booking/Http/Requests/EventBookRequest.php` | REWRITE | B | Authorize on pitch schedule only |
| `modules/Booking/Http/Requests/ProductEventCrudRequest.php` | DELETE | A | |
| `modules/Booking/Http/Requests/ProductEventRequest.php` | DELETE | A | |
| `modules/Booking/Http/Requests/ProductEventScheduleRequest.php` | DELETE | A | |
| `modules/Booking/Http/Requests/ProductPitchRequest.php` | KEEP | — | Pitch save unchanged |
| `modules/Booking/Listeners/SetPublishedProductsToDraft.php` | REWRITE | E | Remove ProductEventService if unused |
| `modules/Booking/Listeners/UnassignAllProductManagers.php` | REWRITE | E | Same |
| `modules/Booking/Providers/BookingServiceProvider.php` | REWRITE | A | Drop ProductEvent bindings |
| `modules/Booking/Resources/assets/js/Pages/BookingTime.vue` | REWRITE | B | Drop product_event_id prop |
| `modules/Booking/Resources/assets/js/Pages/FrontendEventShow.vue` | DELETE | A | Use FrontendProductShow |
| `modules/Booking/Resources/assets/js/Pages/FrontendOrderReschedule.vue` | REWRITE | B | Drop product event picker |
| `modules/Booking/Resources/assets/js/Pages/FrontendProductShow.vue` | REWRITE | C/D | Direct pitch booking + bookedEvents |
| `modules/Booking/Resources/assets/js/Pages/ProductEdit.vue` | REWRITE | A | Remove Events tab / ProductEventList |
| `modules/Booking/Resources/assets/js/Pages/ProductEventFormModal.vue` | DELETE | A | |
| `modules/Booking/Resources/assets/js/Pages/ProductEventList.vue` | DELETE | A | |
| `modules/Booking/Routes/web.php` | REWRITE | A | Remove admin + booking.events routes |
| `modules/Booking/Rules/BookingWithinPitchWindow.php` | KEEP | B | Still validates pitch window |
| `modules/Booking/Rules/BookingWithinProductEventRange.php` | DELETE | A | |
| `modules/Booking/Rules/NoOverlappingPitchAtLocation.php` | KEEP | — | Uses ProductEventService overlap only |
| `modules/Booking/Services/EventService.php` | KEEP | B/C | Availability math unchanged |
| `modules/Booking/Services/ProductEventCrudService.php` | DELETE | A | Replace with PitchAvailabilityService |
| `modules/Booking/Services/ProductEventService.php` | REWRITE | C/E | Rename/split; remove ProductEvent listing |
| `modules/Ecommerce/Entities/Product.php` | REWRITE | C | Drop productEvents() from policies |
| `modules/Ecommerce/Policies/ProductPolicy.php` | REWRITE | C | showFrontendProductEvent without ProductEvent |
| `modules/Ecommerce/Services/OrderService.php` | REWRITE | B | bookEvent(product, datetime, user) |
| `tests/Feature/PitchLocationFkTest.php` | KEEP | — | Unrelated |
| `tests/Feature/PitchLocationOverlapTest.php` | KEEP | — | Unrelated |
| `tests/Feature/PitchDirectBookingTest.php` | KEEP | 1→B | Target tests (RED until T8.2) |
| `tests/Feature/PitchAvailabilityListingTest.php` | KEEP | 1→C | Target tests (RED until T8.3) |
| `tests/Feature/PitchPublicEventCalendarTest.php` | KEEP | 1→D | Target tests (RED until T8.4) |
| `tests/Feature/ProductEventRemovalTest.php` | KEEP | 1→A | Target tests (RED until T8.1) |
| `tests/Concerns/CreatesBookablePitch.php` | KEEP | 1 | Test helper |

**Summary:** DELETE 16 · REWRITE 24 · KEEP 14

Regenerate inventory after each phase:

```bash
rg -l 'ProductEvent|product_events|product_event_id' --glob '!vendor/**' --glob '!node_modules/**' | sort > migration-preparation/artifacts/product-event-blast-radius.txt
```
