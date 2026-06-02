# Feature Dependency Map — optim8-buskincity

> Generated: 2026-04-09  
> Purpose: Migration preparation — per-feature breakdown of controllers, services, models/tables, and external integrations.

---

## Feature 1 — Authentication & Session Management

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `RegisteredUserController` | `App\Http\Controllers` | Handles new user registration via form |
| `NewPasswordController` | `App\Http\Controllers` | Resets password from token link |
| `PasswordResetLinkController` | `App\Http\Controllers` | Sends forgot-password email |
| `TwoFactorAuthenticatedSessionController` | `App\Http\Controllers` | Handles 2FA challenge submission |
| `VerifyEmailController` | `App\Http\Controllers` | Processes signed email verification link |
| `CustomOAuthController` | `App\Http\Controllers` | Handles social OAuth callback |
| `AuthenticatedSessionController` | `Laravel\Fortify` | Login/logout session management |
| `EmailVerificationPromptController` | `Laravel\Fortify` | Shows "verify your email" page |
| `Frontend\AuthTestController` | `App\Http\Controllers\Frontend` | Dev-only login test helper |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `App\Services\LoginService` | Manages login flow context (admin vs frontend), session `home_url`, socialite driver resolution |
| `App\Services\ResetPasswordService` | Wraps password reset token generation |
| `App\Services\SettingService` | Reads reCAPTCHA keys, socialite driver config from DB |
| `App\Actions\Fortify\CreateNewUser` | Fortify hook — creates user row on registration |
| `App\Actions\Fortify\RedirectIfTwoFactorAuthenticatable` | Fortify hook — decides if 2FA step is needed |
| `App\Actions\Fortify\PrepareAuthenticatedSession` | Fortify hook — post-login session setup |
| `App\Actions\Fortify\ResetUserPassword` | Fortify hook — executes password reset |
| `App\Actions\Fortify\UpdateUserPassword` | Fortify hook — handles password change |
| `App\Actions\Socialstream\CreateUserFromProvider` | Creates a new user from OAuth provider data |
| `App\Actions\Socialstream\CreateConnectedAccount` | Stores OAuth connected account record |
| `App\Actions\Socialstream\UpdateConnectedAccount` | Updates OAuth token on re-auth |
| `App\Actions\Socialstream\ResolveSocialiteUser` | Resolves provider user data via Socialite |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `App\Models\User` | `users` | Core user record; includes 2FA columns, profile photo FK, language FK |
| `App\Models\ConnectedAccount` | `connected_accounts` | OAuth provider tokens (Google, Facebook, Twitter) |
| `App\Models\UserMeta` | `user_metas` | Stores stripe account ID, profile fields, etc. |
| — | `password_resets` | Laravel password reset tokens table |
| — | `personal_access_tokens` | Sanctum auth tokens |
| — | `sessions` | Database session storage |

### External Integrations

| Integration | Package / Service | Usage |
|-------------|------------------|-------|
| **Google reCAPTCHA v3** | `google/recaptcha` (via `SettingService`) | Applied on login, register, forgot-password, 2FA |
| **Laravel Fortify** | `laravel/fortify` | Auth scaffolding — login, 2FA, email verification, password reset |
| **JoelButcher Socialstream** | `joelbutcher/socialstream` | OAuth social login orchestration |
| **Laravel Socialite** | `laravel/socialite` | Resolves OAuth user from Google/Facebook/Twitter |
| **Laravel Sanctum** | `laravel/sanctum` | API auth tokens and SPA session authentication |
| **Queue (Mail)** | Laravel Queue | Password reset emails dispatched via queue |

---

## Feature 2 — User Management

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `UserController` | `App\Http\Controllers` | CRUD, password update, suspension, trashed record management |
| `RoleController` | `App\Http\Controllers` | Role CRUD with permission assignments |
| `SendUserPasswordResetEmailController` | `App\Http\Controllers` | Admin-triggered password reset email |
| `UserProfileController` | `App\Http\Controllers` | Profile view for admin and frontend |
| `UserPasswordController` | `App\Http\Controllers` | Frontend self-service password setter |
| `Api\CityController` | `App\Http\Controllers\Api` | Lists all cities (for dropdowns) |
| `Api\CityUserController` | `App\Http\Controllers\Api` | Reads/writes city assignments for a user |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `App\Services\UserService` | Paginated user listing, trashed records, reassign/delete resources on delete, role options |
| `App\Services\RoleService` | Role CRUD, permission assignment, seeding |
| `App\Services\UserProfileService` | Builds profile page data and meta |
| `App\Services\CityService` | City lookup, geolocation-to-city resolution |
| `App\Actions\Fortify\UpdateUserProfileInformation` | Fortify hook — updates user name and email, dispatches verification |
| `App\Jobs\SendResetPasswordLink` | Queued job — sends admin-triggered reset email |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `App\Models\User` | `users` | Soft-deletes, `is_suspended` flag, `unique_key` |
| `App\Models\UserMeta` | `user_metas` | Arbitrary key-value meta (stripe_account_id, etc.) |
| `App\Models\Role` | `roles` | Spatie roles |
| `App\Models\Permission` | `permissions` | Spatie permissions |
| `App\Models\City` | `cities` | BuskinCity-specific city entities |
| — | `city_user` | Pivot: user ↔ city assignments |
| — | `model_has_roles` | Spatie pivot: user ↔ role |
| — | `model_has_permissions` | Spatie pivot: user ↔ permission |
| — | `role_has_permissions` | Spatie pivot: role ↔ permission |

### External Integrations

| Integration | Package / Service | Usage |
|-------------|------------------|-------|
| **Spatie Laravel Permission** | `spatie/laravel-permission` | Role / permission assignment and middleware gates |
| **Queue (Mail)** | Laravel Queue | Admin password reset email queued via `SendResetPasswordLink` job |
| **Laravel Mail** | — | `ResetPasswordPerformer` Mailable for performer-specific reset emails |

---

## Feature 3 — Frontend / Public Pages

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `Frontend\PageController` | `App\Http\Controllers\Frontend` | Renders homepage and dynamic CMS page by slug |
| `Frontend\PostController` | `App\Http\Controllers\Frontend` | Blog index and single post view |
| `Frontend\PostCategoryController` | `App\Http\Controllers\Frontend` | Post listing by category |
| `Frontend\ProfileController` | `App\Http\Controllers\Frontend` | Public user profile page |
| `Frontend\QrCodeController` | `App\Http\Controllers\Frontend` | Printable QR code page for user |
| `SitemapController` | `App\Http\Controllers` | XML sitemap index and URL sitemaps |
| `ChangeLanguageController` | `App\Http\Controllers` | Language switcher — persists locale |
| `StylePageBuilderController` | `App\Http\Controllers` | Serves per-page CSS generated by page builder |
| `StoredCssController` | `App\Http\Controllers` | Serves compiled/stored CSS (theme, email) |
| `ApiPageBuilderComponentUserListController` | `App\Http\Controllers` | Public API for page builder user-list component |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `App\Services\PageService` | Resolves page by translation slug, builds page builder component data |
| `App\Services\PostService` | Paginated post listing, single post resolution, category filtering |
| `App\Services\CategoryService` | Category listing with post counts |
| `App\Services\SitemapService` | Generates sitemap entries for pages, posts, profiles, spaces |
| `App\Services\SettingService` | Reads home page setting, header/footer config, theme CSS URLs |
| `App\Services\LanguageService` | Available locale resolution, locale switching |
| `App\Services\UserProfileService` | Builds profile page props (user meta, products, upcoming events) |
| `App\Services\IPRegistryService` | Resolves visitor IP to geolocation data for language/city detection |
| `App\Services\IPService` | Wraps IP detection logic, falls back across providers |
| `App\Facades\Localization` | Locale-aware URL generation via `mcamara/laravel-localization` |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `App\Models\Page` | `pages` | CMS page with author FK |
| `App\Models\PageTranslation` | `page_translations` | Translated content + page builder JSON per locale |
| `App\Models\Post` | `posts` | Blog posts with status, scheduled publish |
| `App\Models\Category` | `categories` | Post categories |
| `App\Models\CategoryTranslation` | `category_translations` | Translated category names and slugs |
| `App\Models\Language` | `languages` | Supported locales configuration |
| `App\Models\User` | `users` | Profile page source; `unique_key` is the public slug |
| `App\Models\UserMeta` | `user_metas` | Profile page meta fields (bio, social links, etc.) |
| `App\Models\Media` | `media` | Profile photos, page thumbnails |
| `App\Models\Setting` | `settings` | Home page, SEO defaults, header/footer config |
| — | `event_calendars` (view) | DB view used by Events Calendar page builder component |

### External Integrations

| Integration | Package / Service | Usage |
|-------------|------------------|-------|
| **mcamara/laravel-localization** | Composer package | Multi-locale URL routing and redirect filter |
| **Cloudinary** | `cloudinary-labs/cloudinary-laravel` | Optimized image URLs for profile photos and media |
| **IPRegistry API** | `https://api.ipregistry.co/` | Visitor IP-to-country/city resolution for locale detection |
| **torann/geoip** | Composer package | GeoIP abstraction layer (backed by IPRegistry) |
| **Google Fonts** | External CDN | Font URLs generated dynamically from settings |

---

## Feature 4 — Admin CMS (Pages, Posts, Categories, Media)

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `PageController` | `App\Http\Controllers` | Pages CRUD, duplicate, translation delete, menu usage check |
| `PostController` | `App\Http\Controllers` | Posts CRUD with scheduling, tags, media |
| `CategoryController` | `App\Http\Controllers` | Category CRUD with translations |
| `MediaController` | `App\Http\Controllers` | Media CRUD, image edit/replace, API upload |
| `ApiPageBuilderController` | `App\Http\Controllers` | Options API for page builder components (type-options, user-list role-options, post category-options) |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `App\Services\PageService` | Page CRUD, builder component resolution, translation management, menu dependency check |
| `App\Services\PostService` | Post CRUD, scheduling, tag management, category assignment |
| `App\Services\CategoryService` | Category tree management, translation handling |
| `App\Services\MediaService` | Upload, rename, replace, delete media; Cloudinary integration; unique filename generation |
| `App\Services\PageBuilderService` | Resolves page builder component schemas and form props |
| `App\Services\MenuService` | Checks if a page/space is used in any navigation menu |
| `App\Jobs\ProcessPublishScheduledPost` | Triggered by scheduler to publish posts with future `published_at` |
| `App\Jobs\DeleteMediaFromStorage` | Async media deletion from Cloudinary after DB record removal |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `App\Models\Page` | `pages` | Author FK, status (draft/published) |
| `App\Models\PageTranslation` | `page_translations` | Per-locale: title, slug, content JSON |
| `App\Models\Post` | `posts` | Author, status, scheduled publish, category pivot |
| `App\Models\Category` | `categories` | Hierarchical categories |
| `App\Models\CategoryTranslation` | `category_translations` | Translated name/slug |
| `App\Models\Media` | `media` | File metadata + Cloudinary references |
| `App\Models\MediaTranslation` | `media_translations` | Alt text and description per locale |
| `App\Models\Mediable` | `mediables` | Polymorphic media attachments (morphMany) |
| — | `category_post` | Pivot: post ↔ category |

### External Integrations

| Integration | Package / Service | Usage |
|-------------|------------------|-------|
| **Cloudinary** | `cloudinary-labs/cloudinary-laravel` | Primary file storage and CDN for all uploaded media |
| **TinyMCE** | External JS (API key from Settings) | Rich text editor for post/page content |
| **Queue** | Laravel Queue | `DeleteMediaFromStorage`, `ProcessPublishScheduledPost` |

---

## Feature 5 — Dashboard & Admin Widgets

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `DashboardController` | `App\Http\Controllers` | Admin dashboard page — injects widget config |
| `Frontend\DashboardController` | `App\Http\Controllers\Frontend` | Authenticated user dashboard (performer home) |
| `ApiWidgetController` | `App\Http\Controllers` | Latest registrations widget, stored widget data |
| `Booking\ApiWidgetController` | `Modules\Booking\Http\Controllers` | Latest bookings widget |
| `FormBuilder\ApiWidgetController` | `Modules\FormBuilder\Http\Controllers` | Form entries widget |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `App\Services\WidgetService` | Widget data storage and retrieval (JSON blobs in `global_options`) |
| `App\Services\WidgetFrontendService` | Frontend user dashboard data composition |
| `App\Services\WidgetFrontendBuskincityService` | BuskinCity-specific extensions to the frontend dashboard |
| `App\Services\UserService` | `getLatestRegistrations()` — powers the registrations widget |
| `App\Services\SettingService` | `adminDashboardWidgets()` — returns widget configuration from settings |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `App\Models\User` | `users` | Source for latest-registration widget |
| `App\Models\GlobalOption` | `global_options` | Stores cached widget data blobs by UUID |
| `Modules\Ecommerce\Entities\Order` | `orders` (via Lunar) | Source for booking widget |
| `Modules\FormBuilder\Entities\FormEntry` | `form_entries` | Source for form entry widget |

### External Integrations

_None unique to this feature. Inherits Queue and Cloudinary from other features._

---

## Feature 6 — Theme & Appearance Settings

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `ThemeColorController` | `App\Http\Controllers` | Read/write color scheme settings |
| `ThemeHeaderController` | `App\Http\Controllers` | Header layout and media |
| `ThemeHeaderNavigationController` | `App\Http\Controllers` | Header navigation menu items |
| `ThemeFooterController` | `App\Http\Controllers` | Footer layout, social links, media |
| `ThemeFooterNavigationController` | `App\Http\Controllers` | Footer navigation menu items |
| `ThemeAdvanceController` | `App\Http\Controllers` | Advanced CSS, tracking codes, additional scripts |
| `ThemeFontController` | `App\Http\Controllers` | Font family, size settings |
| `ThemeSeoController` | `App\Http\Controllers` | Global SEO defaults (OG image, post thumbnail, meta) |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `App\Services\SettingService` | Core persistence for all theme settings (`saveKey`, grouped reads) |
| `App\Services\ThemeService` | Theme CSS generation helpers, SASS compilation triggers |
| `App\Services\MenuService` | Navigation menu build/save for header and footer items |
| `App\Services\MediaService` | Logo, favicon, OG image upload and storage |
| `App\Services\StripeService` | Syncs brand colors/logo to Stripe connected accounts |
| `App\Services\StripeSettingService` | Reads/writes Stripe branding-specific settings |
| `App\Jobs\CompileThemeCss` | Queued — recompiles CSS after theme settings are saved |
| `App\Jobs\UpdateStripeConnectedAccountBrandingLogo` | Queued — pushes new logo to all Stripe connected accounts |
| `App\Jobs\UpdateStripeConnectedAccountColor` | Queued — pushes updated colors to all Stripe connected accounts |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `App\Models\Setting` | `settings` | Key-value store for all theme settings (grouped by `theme_color`, `font`, `font_size`, `header`, `footer`, `theme_seo`, `additional_code`, `tracking_code`) |
| `App\Models\Media` | `media` | Logo, favicon, post thumbnail, OG image |
| `App\Models\Menu` | `menus` | Header and footer navigation menus |
| `App\Models\MenuItem` | `menu_items` | Individual nav items (page, space, URL, etc.) |

### External Integrations

| Integration | Package / Service | Usage |
|-------------|------------------|-------|
| **Stripe API** | `stripe/stripe-php` | Brand color and logo sync to connected accounts |
| **Cloudinary** | `cloudinary-labs/cloudinary-laravel` | Logo/favicon image fetching for Stripe upload |
| **Google Fonts API** | External CDN | Font family URL generation for `<link>` tags |
| **Queue** | Laravel Queue | CSS compilation and Stripe sync jobs |

---

## Feature 7 — System Settings

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `LanguageController` | `App\Http\Controllers` | Language list management (enable/disable/reorder) |
| `TranslationManagerController` | `App\Http\Controllers` | Translation CRUD, import/export |
| `SettingKeyController` | `App\Http\Controllers` | API keys management (Stripe, reCAPTCHA, OAuth, TinyMCE, IPRegistry, etc.) |
| `CookieConsentController` | `App\Http\Controllers` | Cookie consent enable/disable and message |
| `ModuleController` | `App\Http\Controllers` | Module enable/disable, navigation config, activation confirmation |
| `StripeController` | `App\Http\Controllers` | Admin-level Stripe global settings (fees, payment methods, keys) |
| `ApiOptionController` | `App\Http\Controllers` | Dropdowns: phone countries, countries, timezones |
| `ApiSettingController` | `App\Http\Controllers` | Returns max file size setting |
| `Api\CityController` | `App\Http\Controllers\Api` | Cities list for dropdowns |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `App\Services\SettingService` | Central setting persistence — `saveKey()`, group reads, cached lookups |
| `App\Services\LanguageService` | Language list management, locale code validation |
| `App\Services\TranslationManagerService` | Translation file read/write, import from JSON, export to downloadable file |
| `App\Services\TranslationService` | Low-level translation key resolution |
| `App\Services\ModuleService` | Module activate/deactivate, navigation updates, event dispatch |
| `App\Services\CountryService` | Country list from `countries` table + ISO data |
| `App\Services\CityService` | City entity management; geolocation-to-city matching |
| `App\Services\StripeSettingService` | Stripe-specific setting reads (fees, payment methods, country specs) |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `App\Models\Setting` | `settings` | All configuration — key/value with group classification |
| `App\Models\Language` | `languages` | Enabled locales with ordering |
| `App\Models\Translation` | `translations` | Custom translation key/value strings |
| `App\Models\Module` | `modules` | Module state and navigation config JSON |
| `App\Models\Country` | `countries` | ISO country data (alpha2, display name) |
| `App\Models\City` | `cities` | City entities with geolocation data |

### External Integrations

| Integration | Package / Service | Usage |
|-------------|------------------|-------|
| **Stripe API** | `stripe/stripe-php` | Retrieves Stripe country specs when not yet cached |
| **mcamara/laravel-localization** | Composer package | Locale detection, redirect filter, translation routes |
| **Google reCAPTCHA** | External API | Key stored and validated here |
| **IPRegistry API** | External API | Key stored here; consumed by geo-detection |
| **TinyMCE** | External JS | API key stored here; served to editor |
| **OAuth Providers** (Google, Facebook, Twitter) | Laravel Socialite | Client ID/Secret stored in settings |
| **FontAwesome** | External CDN | Kit name stored in settings |

---

## Feature 8 — Logging & Error Monitoring

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `SystemLogController` | `App\Http\Controllers` | Lists system activity logs (via Laravel Telescope) |
| `ErrorLogController` | `App\Http\Controllers` | Lists, deletes, bulk-deletes internal error log entries |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `App\Services\ErrorLogService` | Creates error log entries, provides search/delete methods |
| `App\Listeners\SendErrorReportNotification` | Listens to `ErrorReport` event; sends Telegram notification |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `App\Models\ErrorLog` | `error_logs` | Captured application errors with stack traces |
| `App\Models\Cron` | `crons` | Cron execution log records |
| — | `telescope_entries` | Laravel Telescope records (watched separately) |
| — | `telescope_entries_tags` | Telescope tags for filtering |
| — | `telescope_monitoring` | Telescope monitoring records |

### External Integrations

| Integration | Package / Service | Usage |
|-------------|------------------|-------|
| **Laravel Telescope** | `laravel/telescope` | Activity/request/query logging (disabled in prod behind admin redirect) |
| **Telegram Bot API** | HTTP via `SendTrialCronTelegramMessage` command | Sends error digest message; `ErrorReport` event dispatched daily |
| **Queue** | Laravel Queue | Error notifications queued |

---

## Feature 9 — Payments & Stripe Integration

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `Frontend\PaymentController` | `App\Http\Controllers\Frontend` | Stripe payment dashboard page for performers |
| `Frontend\StripeController` | `App\Http\Controllers\Frontend` | Create connected account, onboarding link, settings, dashboard redirect |
| `WebhookStripeController` | `App\Http\Controllers` | Receives and stores Stripe webhook events |
| `StripeController` (admin) | `App\Http\Controllers` | Admin-level Stripe global configuration page |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `App\Services\StripeService` | Full Stripe client wrapper: create Express accounts, account links, balance, transactions, checkout sessions, webhook verification, branding sync |
| `App\Services\StripeSettingService` | Reads/writes Stripe-specific settings (primary/secondary color, payment methods, country specs, fee percent) |
| `App\Services\SettingService` | Retrieves Stripe API keys from settings cache |
| `App\Entities\UserMetaStripe` | Entity wrapping a User's Stripe meta — `getAccountId()`, `isEnabled()` |
| `App\Jobs\UpdateStripeConnectedAccountBrandingLogo` | Pushes platform logo to each connected Stripe account |
| `App\Jobs\UpdateStripeConnectedAccountColor` | Pushes primary/secondary color to each connected Stripe account |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `App\Models\User` | `users` | Account owner |
| `App\Models\UserMeta` | `user_metas` | `stripe_account_id`, `stripe_enabled`, `stripe_currency` stored here |
| `App\Models\PaymentWebhook` | `payment_webhooks` | Stores raw Stripe webhook payloads and event type |
| `App\Models\Country` | `countries` | Used to filter valid Stripe country specs |
| `App\Models\Setting` | `settings` | Stripe keys (`stripe_sk`, `stripe_pk`, `stripe_endpoint_secret`), fee percent, payment methods, country specs cache |

### External Integrations

| Integration | Package / Service | Usage |
|-------------|------------------|-------|
| **Stripe API** | `stripe/stripe-php` | Connected accounts, account links, login links, balance, transactions, checkout sessions, webhooks |
| **Cloudinary** | `cloudinary-labs/cloudinary-laravel` | Fetches logo image to upload as Stripe business logo |
| **Queue (Mail)** | Laravel Queue | Donation thank-you email (`ThankYouCheckoutCompleted`) queued on `checkout.session.completed` webhook |

---

## Feature 10 — Donations

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `Frontend\DonationController` | `App\Http\Controllers\Frontend` | Initiates Stripe Checkout for a user; handles success redirect |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `App\Services\StripeService` | `checkout()` — creates a Stripe Checkout Session targeting the performer's connected account |
| `App\Services\SettingService` | Reads Stripe keys, payment method types, application fee percentage |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `App\Models\User` | `users` | Donation recipient; `unique_key` used in URL |
| `App\Models\UserMeta` | `user_metas` | `stripe_account_id`, `stripe_currency` used for checkout |
| `App\Models\PaymentWebhook` | `payment_webhooks` | Stripe `checkout.session.completed` event stored here |

### External Integrations

| Integration | Package / Service | Usage |
|-------------|------------------|-------|
| **Stripe Checkout** | `stripe/stripe-php` | Hosted checkout page with Stripe Connect (destination charge to performer) |
| **Queue (Mail)** | Laravel Queue | `ThankYouCheckoutCompleted` mailable queued after successful webhook |
| **throttle:checkout** | Laravel Rate Limiter | 1 checkout attempt per minute per user/IP |

---

## Feature 11 — Booking (Module)

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `ProductController` | `Modules\Booking\Http\Controllers` | Admin CRUD for bookable products (service listings) |
| `ProductEventController` | `Modules\Booking\Http\Controllers` | Updates a product's event settings |
| `ProductEventCrudController` | `Modules\Booking\Http\Controllers` | Admin CRUD for individual event instances on a product |
| `ProductEventScheduleController` | `Modules\Booking\Http\Controllers` | Admin read/write of a product event's recurring schedule |
| `ProductSpaceController` | `Modules\Booking\Http\Controllers` | Associates a product with a Space |
| `OrderController` (admin) | `Modules\Booking\Http\Controllers` | Admin order listing, detail, cancel, reschedule, available-times |
| `SettingController` | `Modules\Booking\Http\Controllers` | Booking module settings (super-admin only) |
| `ApiWidgetController` | `Modules\Booking\Http\Controllers` | Latest bookings widget API |
| `Frontend\ProductController` | `Modules\Booking\Http\Controllers\Frontend` | Authenticated performer: product listing, detail, allowed-dates, available-times |
| `Frontend\EventController` | `Modules\Booking\Http\Controllers\Frontend` | Authenticated performer: single event detail, available/allowed times |
| `Frontend\OrderController` | `Modules\Booking\Http\Controllers\Frontend` | Authenticated performer: order list, detail, reschedule, book-event, book-event-batch |
| `Frontend\CheckInController` | `Modules\Booking\Http\Controllers\Frontend` | Marks an order attendee as checked in |
| `Frontend\UpcomingEventController` | `Modules\Booking\Http\Controllers\Frontend` | API: upcoming events for a performer by unique key |
| `ApiPageBuilderComponent\EventsCalendarController` | `Modules\Booking\Http\Controllers` | Page-builder component: events calendar data + location options |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `Modules\Booking\Services\ProductEventService` | Core booking logic: availability calculation, allowed dates, time slot resolution, time restriction enforcement, schedule-based conflict detection |
| `Modules\Booking\Services\ProductEventCrudService` | Product event creation/update; recurrence rule management; batch booking |
| `Modules\Booking\Services\EventService` | Wraps `ProductEventService` for the standalone event context |
| `Modules\Booking\Services\EventsCalendarService` | Aggregates events for the calendar page-builder component; applies location/city filters |
| `Modules\Booking\Services\ProductSpaceService` | Links/unlinks a Space to a product |
| `Modules\Booking\Services\SettingService` | Reads booking module settings |
| `Modules\Ecommerce\Services\OrderService` | Order creation, cancellation, reschedule, status transitions |
| `Modules\Ecommerce\Services\ProductService` | Product CRUD, manager assignment, price resolution |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `Modules\Ecommerce\Entities\Product` | `products` (Lunar) | Core product; productable morph pointing to booking event config |
| `Modules\Ecommerce\Entities\ProductMeta` | `products_meta` | Arbitrary product key-value meta |
| `Modules\Ecommerce\Entities\Order` | `orders` (Lunar) | Booking order records |
| `Modules\Ecommerce\Entities\OrderLine` | `order_lines` (Lunar) | Line items on an order |
| `Modules\Booking\Entities\Event` | `events` | Booking event configuration per product |
| `Modules\Booking\Entities\EventCalendar` | `event_calendars` (view) | DB view joining events + schedules for calendar display |
| `Modules\Booking\Entities\ProductEvent` | `product_events` | Individual event instances with date/time |
| `Modules\Booking\Entities\ProductEventTranslation` | `product_event_translations` | Translated event title/description |
| `Modules\Booking\Entities\Schedule` | `schedules` | Recurring scheduling config for a product |
| `Modules\Booking\Entities\ScheduleRule` | `schedule_rules` | Day-of-week / date-based rules within a schedule |
| `Modules\Booking\Entities\ScheduleRuleTime` | `schedule_rule_times` | Time slots within a schedule rule |
| `Modules\Booking\Entities\OrderCheckIn` | `order_check_ins` | Check-in records per order attendee |
| `Modules\Space\Entities\Space` | `spaces` | Optional space association for bookable location |

### Events & Listeners

| Event | Listeners | Trigger |
|-------|-----------|---------|
| `Booking\Events\EventBooked` | `SendBookedEventNotification` → sends `EventBooked` mailable | On successful order placement |
| `Booking\Events\EventCanceled` | `SendCanceledEventNotification`, `CancelUpcomingOrOngoingBookings` | On order cancellation |
| `Booking\Events\EventRescheduled` | `SendRescheduledEventNotification` → sends `EventRescheduled` mailable | On order reschedule |
| `Booking\Events\ModuleDeactivated` | `SetPublishedProductsToDraft`, `UnassignAllProductManagers`, `UnassignSpaceFromProduct` | On module deactivation |

### External Integrations

| Integration | Package / Service | Usage |
|-------------|------------------|-------|
| **Lunar (GetCandy)** | `lunarphp/lunar` | Underlying product/order/pricing engine |
| **Queue (Mail)** | Laravel Queue | Booking confirmation, cancellation, reschedule emails |
| **Laravel Mail** | — | `EventBooked`, `EventCanceled`, `EventRescheduled`, `EventReminder` Mailables |

---

## Feature 12 — Spaces (Module)

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `SpaceController` | `Modules\Space\Http\Controllers` | Admin CRUD for spaces, manager assignment, search-managers, menu usage check |
| `EventController` | `Modules\Space\Http\Controllers` | Admin CRUD for space events (recurring event calendar tied to a space) |
| `PageController` | `Modules\Space\Http\Controllers` | Admin read/write for space sub-pages |
| `SettingController` | `Modules\Space\Http\Controllers` | Space module settings (admin-only; super-admin gate) |
| `SpaceTypeController` | `Modules\Space\Http\Controllers` | Space type CRUD (category/type of space) |
| `ContactController` | `Modules\Space\Http\Controllers` | API endpoint to validate contact form on a space |
| `Frontend\SpaceController` | `Modules\Space\Http\Controllers\Frontend` | Public space directory and individual space page |
| `Frontend\SpaceEventController` | `Modules\Space\Http\Controllers\Frontend` | API: events for a specific space (calendar widget feed) |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `Modules\Space\Services\SpaceService` | Space CRUD, manager assignment, slug generation, form data building, menu usage check |
| `Modules\Space\Services\EventService` | Space-level event CRUD with optional recurrence (separate from Booking event) |
| `Modules\Space\Services\SpaceEventService` | Resolves upcoming events for a space for the frontend calendar widget |
| `Modules\Space\Services\PageSpaceService` | Manages space sub-pages (content sections on a space profile) |
| `App\Services\CityService` | City lookup used when a space or space event references a city |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `Modules\Space\Entities\Space` | `spaces` | Venue entity; `city_id` FK added in BuskinCity migration |
| `Modules\Space\Entities\SpaceTranslation` | `space_translations` | Translated name, slug, description per locale |
| `Modules\Space\Entities\SpaceEvent` | `space_events` | Events attached to a space; `city_id` FK |
| `Modules\Space\Entities\SpaceEventTranslation` | `space_event_translations` | Translated event details |
| `Modules\Space\Entities\Page` | `space_pages` | Sub-pages within a space profile |
| `Modules\Space\Entities\PageTranslation` | `space_page_translations` | Translated sub-page content |
| `App\Models\City` | `cities` | City FK on spaces and space events |
| — | `space_user` | Pivot: space ↔ manager user |

### Events & Listeners

| Event | Listeners | Trigger |
|-------|-----------|---------|
| `Space\Events\ModuleDeactivated` | `RemoveSpaceFromMenus`, `SetPublishedEventsDrafts`, `SetPublishedPageTranslationsDrafts`, `UnassignAllSpaceManagers`, `UnassignSpaceFromProduct` | On module deactivation |

### External Integrations

| Integration | Package / Service | Usage |
|-------------|------------------|-------|
| **Cloudinary** | `cloudinary-labs/cloudinary-laravel` | Space cover images and gallery media |
| **mcamara/laravel-localization** | Composer package | Localized space URLs (slug per locale) |

---

## Feature 13 — Form Builder (Module)

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `FormBuilderController` | `Modules\FormBuilder\Http\Controllers` | Admin CRUD for form definitions; public schema endpoint and submit |
| `FormEntryController` | `Modules\FormBuilder\Http\Controllers` | Admin form entries inbox — view, mark read/unread, archive, restore, force-delete, bulk actions |
| `AutomateUserCreationController` | `Modules\FormBuilder\Http\Controllers` | Save field mappings; trigger user create/update from form entry; confirm action |
| `SettingController` | `Modules\FormBuilder\Http\Controllers` | Form-level general settings |
| `SettingNotificationController` | `Modules\FormBuilder\Http\Controllers` | CRUD for email notification webhooks per form |
| `PageBuilderController` | `Modules\FormBuilder\Http\Controllers` | API: form options for page builder component |
| `ApiWidgetController` | `Modules\FormBuilder\Http\Controllers` | Form entry count widget API |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `Modules\FormBuilder\Services\FormBuilderService` | Form schema resolution, submission validation, entry creation, file upload handling |
| `Modules\FormBuilder\Services\FormEntryService` | Entry CRUD, bulk status transitions, archive/restore/force-delete |
| `Modules\FormBuilder\Services\AutomateUserCreationService` | Maps form fields → user fields; creates or updates `User` from form entry data |
| `Modules\FormBuilder\Services\SettingNotificationService` | Notification webhook CRUD, recipient resolution |
| `Modules\FormBuilder\Services\MediaService` | Handles file field uploads within form submissions |
| `App\Services\FormService` | Legacy core form schema resolver (used by `FormController` in `routes/web.php`) |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `Modules\FormBuilder\Entities\Form` | `form_builders` | Form definition with field schema JSON |
| `Modules\FormBuilder\Entities\FormEntry` | `form_entries` | Submitted form responses with soft deletes |
| `Modules\FormBuilder\Entities\FormNotificationSetting` | `form_notification_settings` | Email recipients per form |
| `Modules\FormBuilder\Entities\FormMappingRule` | `form_mapping_rules` | Automate user creation field-mapping rules |
| `Modules\FormBuilder\Entities\FieldGroup` | (via `field_groups`) | Reusable field group definitions |
| — | `form_entries_meta` | Entry metadata (file paths, extra values) |
| `App\Models\Form` | `forms` | Legacy shortcode-based forms (separate from FormBuilder module) |
| `App\Models\FieldGroup` | `field_groups` | Core field group definitions |

### Events & Listeners

| Event | Listeners | Trigger |
|-------|-----------|---------|
| `FormBuilder\Events\FormSubmitted` | `SendFormNotification` → sends `FormNotification` mailable to configured recipients | On every accepted form submission |
| `FormBuilder\Events\ModuleDeactivated` | `DeactivateAllNotificationSettings` | On module deactivation |

### External Integrations

| Integration | Package / Service | Usage |
|-------------|------------------|-------|
| **Google reCAPTCHA** | External API | Applied on public form submission |
| **Queue (Mail)** | Laravel Queue | `FormNotification`, `AutomateUserCreationEmail`, `AutomateUserUpdateEmail` mailables |
| **Cloudinary** | `cloudinary-labs/cloudinary-laravel` | File field uploads stored via Cloudinary |
| **throttle:defaultRequest** | Laravel Rate Limiter | Rate limit on public form submission |

---

## Feature 14 — Legacy / Core Forms

### Controllers

| Controller | Namespace | Responsibility |
|-----------|-----------|---------------|
| `FormController` | `App\Http\Controllers` | Serves legacy form schema and accepts submissions from shortcode-embedded forms |

### Services / Business Logic

| Class | Purpose |
|-------|---------|
| `App\Services\FormService` | Schema resolution from `forms` table; entry saving and validation |

### Models & Database Tables

| Model | Table | Notes |
|-------|-------|-------|
| `App\Models\Form` | `forms` | Legacy shortcode form definitions |
| `App\Models\FieldGroup` | `field_groups` | Shared field group definitions used by legacy forms |

### External Integrations

_None unique to this feature._

---

## Cross-Cutting Concerns Summary

### Shared Infrastructure

| Concern | Implementation |
|---------|---------------|
| **Settings / Configuration Store** | `App\Models\Setting` (`settings` table) + `App\Services\SettingService` — used by every feature; cached via `App\Entities\Caches\SettingCache` |
| **Media Storage** | `App\Models\Media` + `App\Services\MediaService` + Cloudinary — shared across CMS, Profile, Space, FormBuilder, Theme |
| **Multi-language / i18n** | `App\Models\Language` + `App\Models\Translation` + `mcamara/laravel-localization` — URL routing, content translations, locale detection |
| **Permissions** | `Spatie\Permission` + `App\Models\Role` + `App\Models\Permission` — applied via `can:`, `role:`, policy gates across all features |
| **Queue / Jobs** | Laravel Queue + Redis — mail, CSS compilation, Stripe sync, media deletion |
| **Authentication Guard** | `auth:sanctum` — used on all protected routes |

### External Services Used Across Multiple Features

| Service | Used By |
|---------|---------|
| **Stripe** | Payments, Donations, Theme (branding sync), System Settings (key storage) |
| **Cloudinary** | CMS Media, Spaces, FormBuilder, Theme, Payments (logo fetch) |
| **Google reCAPTCHA** | Auth, FormBuilder |
| **IPRegistry** | Frontend / Public Pages (locale detection), User Profile |
| **Google Fonts** | Theme & Appearance, Frontend pages |
| **Laravel Mail** | Auth, Bookings, Donations, FormBuilder |
| **Lunar/GetCandy** | Booking, Ecommerce |

---

*End of Feature Dependency Map*
