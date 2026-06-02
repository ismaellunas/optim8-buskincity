# Payments & Stripe Integration — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — full trace of the Stripe Connect (Express) architecture, including onboarding, point-of-sale checkout, and webhook handling.

---

## 1. Performer Onboarding (Stripe Connect)

The system uses Stripe Connect **Express** accounts to allow performers (users) to receive payments directly.

### Flow: Account Creation (`POST /payments/stripe/create`)
1. **Controller**: `StripeController@createThenRedirect`
2. **Logic**:
    - Checks `UserMetaStripe` for an existing account.
    - **Stripe API**: `accounts->create` with `type: express` and the user's country.
    - **Persistence**: Saves `stripe_account_id` to `user_metas` table.
3. **Redirection**: Creates an `accountLink` via `stripe->accountLinks->create` and performs an `Inertia::location` redirect to Stripe's onboarding UI.

### Flow: Return & Branding Sync (`GET /payments/stripe/return`)
1. **Controller**: `StripeController@return`
2. **Branding Sync**: `StripeService@updateAccountBrandingBasedOnPlatform`
    - Fetches the platform logo from **Cloudinary**.
    - Downloads it to a temporary local file.
    - Uploads it to Stripe via `stripe->files->create`.
    - Updates Stripe Account branding (Logo + Primary/Secondary colors from `settings` table).
3. **Persistence**: Updates the user's Stripe status to indicate onboarding completion.

---

## 2. Donation & Payment Checkout

Checkout sessions are created as "Direct Charges" or "Destination Charges" depending on the module.

### Flow: Creating a Session (`GET /donations/checkout/{user}`)
1. **Service**: `StripeService@checkout`
2. **Logic Decisions**:
    - **Application Fee**: Calculates platform commission using `getApplicationFeeAmount` (from `constants.stripe_fee_percent`).
    - **Currency Handling**: `isZeroDecimal` checks if the currency (like JPY) should omit the cent-to-dollar (x100) conversion.
3. **Session**: Creates `Stripe\Checkout\Session` with a `submit_type => donate`.

---

## 3. Webhook Handling (`WebhookStripeController`)

Webhooks are the primary source of truth for payment status.

### Flow: Incoming Event (`POST /stripe/webhook`)
1. **Validation**: `Webhook::constructEvent` verifies the `HTTP_STRIPE_SIGNATURE` against the `stripe_endpoint_secret`.
2. **Persistence**: Every event is logged in the `payment_webhooks` table for auditability.
3. **Event Catchers**:
    - `checkout.session.completed`: Calls `donationThankYouEmail()`.
4. **Side Effects**: 
    - **Email**: Queues `ThankYouCheckoutCompleted` to the donor.
    - **Association**: `getUserIdFromStripeAccount` looks up the `receiver_id` via the `user_metas` table to link the webhook back to the correct performer.

---

## 4. Hidden Dependencies & Side Effects

| Component | Dependency | Role |
|-----------|------------|------|
| **Branding** | `Cloudinary` | The platform logo is pulled from Cloudinary and passed to Stripe's CDN. |
| **Localization** | `IPService` | Sets the default country during onboarding based on the user's IP address. |
| **Emails** | `ThankYouCheckoutCompleted` | Queued mailable dispatched strictly from the webhook success event. |
| **Meta Engine** | `UserMetaStripe` | A specialized entity that provides a clean API for checking Stripe status on a `User` model. |

---

## 5. Database Trace (Critical Tables)

| Table | Usage |
|-------|-------|
| `user_metas` | Stores `stripe_account_id` and the JSON object representing the account status. |
| `payment_webhooks` | Historical log of all Stripe events (Payload, Type, Receiver). |
| `settings` | Stores the global Stripe Secret Key (`stripe_sk`), Public Key, and Webhook Secret. |

---

## 6. Migration Critical Notes

1. **Webhook Endpoint**: During migration, the Stripe Dashboard **must** be updated with the new production URL for webhooks, or the `payment_webhooks` table will stop receiving data.
2. **Secret Keys**: Ensure the `stripe_sk` and `stripe_endpoint_secret` are updated in the `settings` table. An incorrect webhook secret will cause 400 errors during signature verification.
3. **Cloudinary Temp Dir**: The branding sync uses `sys_get_temp_dir()`. Ensure the PHP process has write access to the system temp directory to handle logo processing.
4. **Stripe Connect Redirects**: Stripe onboarding links use `refresh_url` and `return_url`. These are generated as **Signed Routes**. Ensure the new environment uses the same `APP_KEY`, or existing signed URLs in flight (sent but not returned) will fail validation.

---

*End of Payments & Stripe Integration Execution Flow*
