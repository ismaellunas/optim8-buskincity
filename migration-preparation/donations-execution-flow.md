# Donations — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — trace of the donation system, which operates as a stateless integration between the frontend and Stripe Checkout.

---

## 1. Donation Intent (`Frontend\DonationController`)

Donations are initiated from a performer's profile page.

### Flow: Triggering Checkout (`POST /donations/checkout/{user}`)
1. **Controller**: `DonationController@checkout`
2. **Path**: Receives `amount` and `currency` from a specialized `DonationRequest`.
3. **Execution**: Calls `StripeService@checkout`.
    - **Key Decision**: The system calculates the platform fee (`application_fee_amount`) in real-time based on `config('constants.stripe_fee_percent')`.
    - **Stripe Context**: The checkout session is created for the performer's `connected_account_id`.
4. **Redirection**: Redirects the user's browser to the Stripe-hosted checkout page.

---

## 2. Success Feedback (`Frontend\DonationController`)

### Flow: Return to Platform (`GET /donations/success/{user}`)
1. **Controller**: `DonationController@success`
2. **Action**: Renders the `donation-success` Blade view.
3. **Logic**: This is a non-verifying route (informational). The actual verification happens asynchronously via webhooks.

---

## 3. Persistent Record & Webhooks (`StripeService`)

The system does not have a dedicated `donations` table. Instead, it relies on a polymorphic `payment_webhooks` table for all financial recording.

### Flow: Processing the Event (`POST /stripe/webhook`)
1. **Verification**: `Webhook::constructEvent` ensures the request originated from Stripe.
2. **Identification**: `getUserIdFromStripeAccount` finds the recipient user by looking up the Stripe Account ID in `user_metas`.
3. **Persistence**:
    - **Table**: `payment_webhooks`
    - **Data**: Stores the raw JSON payload, the event type (`checkout.session.completed`), and the `receiver_id`.
4. **Side Effects**:
    - **Queue**: Dispatches `ThankYouCheckoutCompleted` email to the donor's email address (captured from Stripe's `customer_details`).

---

## 4. Hidden Dependencies & Side Effects

| Component | Dependency | Role |
|-----------|------------|------|
| **Validation** | `DonationRequest` | Enforces minimum/maximum donation amounts based on currency-specific config. |
| **Fees** | `constants.stripe_fee_percent` | Hardcoded percentage representing the platform's cut of the donation. |
| **Email** | `Mail::queue()` | Uses Laravel's queue system to handle the "Thank You" email asynchronously. |
| **Logging** | `ErrorLogService` | Any failure during checkout session creation is caught and logged in the `error_logs` table. |

---

## 5. Database Trace (Critical Tables)

| Table | Usage |
|-------|-------|
| `payment_webhooks` | The definitive ledger of all finalized donations. |
| `user_metas` | Crucial for mapping Stripe account IDs back to the internal user ID. |

---

## 6. Migration Critical Notes

1. **Stateless Logic**: Since there is no `Donation` model, there is no "historical state" to migrate other than the `payment_webhooks` records. Losing these records means losing the local history of transactions.
2. **Email Queues**: Ensure the `ThankYouCheckoutCompleted` mailable and its template are correctly registered in the new environment's queue worker.
3. **Currency Config**: The donation logic depends on `config('constants.stripe_minimal_payments')`. If these values differ between environments, users might be able to donate 1 cent in one and be blocked in another.

---

*End of Donations Execution Flow*
