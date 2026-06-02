# System Settings — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — full trace of system-wide configuration, module lifecycle, and translation management.

---

## 1. Module Lifecycle & Extension (`ModuleController`)

The platform's features are heavily modularized, with activation/deactivation impacting the entire request lifecycle.

### Flow: Module Activation (`POST /admin/settings/modules/{module}/activate`)
1. **Controller**: `ModuleController@activate`
2. **Logic**:
    - Updates `is_active = true` in the `modules` table.
    - **Caching**: Flushes `ModuleCache` and `MenuCache` (since activation may add new menu items).
3. **Execution Hook**: `ModuleService@onActivated`
    - Resolves the module's `ModuleService` class.
    - If it implements `ToggleableModuleStatusInterface`, calls the `activated()` method.
    - **Side Effect**: This often triggers database seeding or registration of post-types.

---

## 2. Platform Connectivity & API Keys (`SettingKeyController`)

Centralized storage for external service integrations.

### Flow: Updating Stripe/reCAPTCHA Keys (`POST /admin/settings/keys`)
1. **Controller**: `SettingKeyController@update`
2. **Logic**:
    - Syncs inputs against `config('constants.settings.keys')`.
    - Uses `updateOrCreate` on the `Setting` model.
    - Prefixes keys with `key.` (e.g., `key.stripe_secret_key`).
3. **Hidden Dependency**: `SettingCache` is flushed immediately to ensure the next service call (e.g., a credit card payment) uses the fresh credentials.

---

## 3. Translation Management (`TranslationManagerController`)

Handles localized strings stored in the database, supplementing standard Laravel `.php` files.

### Flow: Updating Strings (`POST /admin/settings/translation-manager`)
1. **Controller**: `TranslationManagerController@update`
2. **Service**: `TranslationManagerService@batchUpdate`
    - Updates key-value pairs in the `translations` table.
3. **Hidden Dependency**: `TranslationCache`
    - Flushes only the affected locale's cache (e.g., flushing `fr` cache after a French update) via `flushLocale()`.

### Flow: CSV Import (`POST /admin/settings/translation-import`)
1. **Dependency**: `Maatwebsite\Excel` (via `TranslationsImport`).
2. **Logic**: Reads CSV and batch-inserts into the `translations` table.
3. **Side Effect**: Collects all affected locales in an array and flushes their respective caches in a loop.

---

## 4. Language & Localization (`LanguageController`)

Manages which languages are available to the public and admin.

1. **Logic**: Updates the list of enabled locales in the `settings` table.
2. **Hidden Dependency**: `Mcamara\LaravelLocalization`. 
    - The `supportedLocales` are often dynamically resolved from this setting in the `AppServiceProvider`.

---

## 5. Hidden Dependencies & Side Effects

| Component | Dependency | Role |
|-----------|------------|------|
| **Module Navigation** | `MenuService` | When a module is activated, its `navigations()` are merged into the admin sidebar. |
| **Translation Fallback** | `TranslationManagerService` | If a key isn't found in a specific locale, it attempts to resolve from the `referenceLocale` (default: English). |
| **API Integration** | `SettingService` | Provides high-level methods like `getStripeKeys()` or `getRecaptchaKeys()` that controllers call to avoid raw DB queries. |
| **Purifier** | `Mews\Purifier` | Used in settings save logic (especially for Cookie Consent messages) to strip malicious scripts. |

---

## 6. Database Trace (Critical Tables)

| Table | Usage |
|-------|-------|
| `modules` | Status (`is_active`), `order`, and serialized `navigations`. |
| `settings` | Stores API keys, enabled locales, and cookie consent configuration. |
| `translations` | Key-value pairs indexed by `locale` and `group`. |

---

## 7. Migration Critical Notes

1. **Studly Case Consistency**: The `ModuleService` relies on `Str::studly()` to resolve folder paths (e.g., `Booking` vs `booking`). Folder names in the new environment must match exactly.
2. **Translation Cache**: If migrating a site with thousands of translations, the first request post-migration might be slow as the `TranslationCache` is rebuilt.
3. **Module Interfaces**: Modules with custom `activated()` logic might expect specific environment variables to be present (e.g., a specific folder for binary exports). These must be migrated along with the code.

---

*End of System Settings Execution Flow*
