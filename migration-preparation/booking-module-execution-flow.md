# Booking (Module) — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — detailed trace of the specialized booking lifecycle, its cross-module dependencies (Ecommerce/Space), and event-driven notification system.

---

## 1. Modular Architecture

The Booking system is not a standalone silo; it acts as a fulfillment layer for the **Ecommerce** module and utilizes the **Space** module for physical venue resolution.

- **Entity Overlap**: Uses `Lunar\Models\Order` as the parent record for all bookings.
- **Service Hub**: `Modules\Ecommerce\Services\OrderService` contains the core factory method `bookEvent()` used by the booking controllers.

---

## 2. Booking Lifecycle: Creation (`OrderController`)

### Flow: One-Click Booking (`POST /booking/orders/{product}/book-event`)
1. **Controller**: `Modules\Booking\Http\Controllers\Frontend\OrderController@bookEvent`
2. **Logic Decision (`bookEvent` service)**:
    - **Currency/Channel**: Resolves defaults from the `Lunar` ecommerce core.
    - **Order Factory**: Creates a `Lunar\Models\Order` with status `COMPLETED` (assuming the booking itself is the "success" event).
    - **Line Item**: Creates an `OrderLine` of type `OrderLineType::EVENT`.
    - **Meta Mapping**: Attaches `duration`, `duration_unit`, and `booked_at` to the order's JSON meta column.
3. **Database Persistence**:
    - **Table**: `ecommerce_orders` (Parent order record).
    - **Table**: `ecommerce_order_lines` (Link to the specific product variant).
    - **Table**: `booking_events` (The specific calendar occurrence).
4. **Side Effects**:
    - **Event Dispatch**: `Modules\Booking\Events\EventBooked`.
    - **Listener**: `SendBookedEventNotification` sends localized emails to the user and the system administrator.

---

## 3. Rescheduling & Cancellation

The system supports a state-based "Replicate and Mark" pattern for rescheduling.

### Flow: Rescheduling (`POST /booking/orders/{order}/reschedule`)
1. **Logic**:
    - **Replicate**: The original `Event` record is replicated.
    - **Status Update**: The **original** record is marked as `RESCHEDULED`. The **new** record is set to `UPCOMING` with the updated `booked_at` timestamp.
    - **Tracking**: Maintains a historical audit trail of the original booking intent.
2. **Side Effects**: Dispatches `EventRescheduled`, triggering a confirmation email to the user.

---

## 4. Check-In Flow

Individual events support QR-based or manual check-in.

### Flow: Check-In (`POST /booking/orders/{order}/check-in`)
1. **Path**: `Modules\Booking\Http\Controllers\Frontend\CheckInController`.
2. **Execution**: Creates a record in `booking_check_ins`.
3. **Logic**: Validates if `is_check_in_required` is enabled on the parent product via `UserMeta`.

---

## 5. Hidden Dependencies & Side Effects

| Component | Dependency | Role |
|-----------|------------|------|
| **Product Link** | `Ecommerce` Module | Every booking MUST be associated with a valid Product and its Variant. |
| **Locations** | `Space` Module | Handles the venue data (Address, Lat/Long) for the booking. |
| **Observers** | `ProductObserver` | Syncs booking availability when a product is updated or deleted. |
| **Timezones** | `CarbonTimeZone` | Hard dependency on the `schedule` table's `timezone` column to display correct local times in emails. |
| **Cleanup** | `ModuleDeactivated` | A critical listener that cancels all upcoming bookings and reverts products to 'Draft' if the Booking module is disabled in settings. |

---

## 6. Database Trace (Critical Tables)

| Table | Usage |
|-------|-------|
| `booking_events` | Stores specific instances of bookings (booked_at, duration, status). |
| `booking_product_events` | Links products to their booking-enabled configurations. |
| `booking_schedules` | Stores recurring or fixed time slot definitions and timezones. |
| `booking_check_ins` | Audit log of attendee arrivals. |
| `ecommerce_orders` | Parent transaction record for all bookings. |

---

## 7. Migration Critical Notes

1. **Lunar IDs**: The system uses `Lunar\Models\ProductVariant`. During migration, ensure the `purchasable_id` and `purchasable_type` in `ecommerce_order_lines` point to the correct migrated IDs in the ecommerce schema.
2. **Timezone Accuracy**: If the server timezone changes during migration, the `booked_at` timestamps in `booking_events` (stored without zone) must be audited relative to the `timezone` stored in `booking_schedules`.
3. **Module Interdependency**: The `Booking` module will fail to boot if the `Ecommerce` module is not registered first. Ensure migration scripts honor the `ModuleService` order.
4. **Legacy Meta**: Some booking data is stored in the `meta` column of `ecommerce_orders`. Any data transformation scripts must parse this JSON to ensure `product_id` and `booked_at` remain accurate.

---

*End of Booking Module Execution Flow*
