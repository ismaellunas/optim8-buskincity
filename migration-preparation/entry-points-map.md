# Entry Points Map — optim8-buskincity

> Generated: 2026-04-09  
> Purpose: Migration preparation — full inventory of routes, controllers, middleware, jobs, and events grouped by feature.

---

## Route Files Overview

| File | Prefix | Description |
|------|--------|-------------|
| `routes/web.php` | `/` | Core frontend + auth web routes |
| `routes/admin.php` | `/admin` | Admin panel web + internal API routes |
| `routes/api.php` | `/api` | Minimal Sanctum user endpoint |
| `routes/web_buskincity.php` | `/` | BuskinCity-specific frontend routes (stub + test) |
| `routes/admin_buskincity.php` | `/admin` | BuskinCity-specific admin routes (stub) |
| `routes/channels.php` | — | Broadcast channel authorization |
| `routes/console.php` | — | Artisan closure commands |
| `modules/Booking/Routes/web.php` | `/admin/booking`, `/booking`, `/api/booking` | Booking module routes |
| `modules/Space/Routes/web.php` | `/admin/spaces`, `/spaces`, `/api/space` | Space module routes |
| `modules/Ecommerce/Routes/web.php` | `/admin/ecommerce` | Ecommerce product manager routes |
| `modules/FormBuilder/Routes/web.php` | `/admin/form-builders`, `/form-builders` | FormBuilder admin + public form routes |

---

## Feature 1 — Authentication & Session Management

**Description:** Handles user login, registration, password reset, email verification, two-factor authentication, and OAuth (social login). Covers both the frontend and admin panel login flows independently.

### Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `POST` | `/login` | `AuthenticatedSessionController@store` | — |
| `POST` | `/register` | `RegisteredUserController@store` | — |
| `POST` | `/forgot-password` | `PasswordResetLinkController@store` | `password.email` |
| `POST` | `/reset-password` | `NewPasswordController@store` | `password.update` |
| `POST` | `/two-factor-challenge` | `TwoFactorAuthenticatedSessionController@store` | — |
| `GET` | `/email/verify/{id}/{hash}` | `VerifyEmailController` | `verification.verify` |
| `GET` | `/oauth/{provider}/callback` | `CustomOAuthController@handleProviderCallback` | `oauth.callback` |
| `GET` | `/admin/login` | `AuthenticatedSessionController@create` (Fortify) | `login` |
| `POST` | `/admin/login` | `AuthenticatedSessionController@store` (Fortify) | `login.attempt` |
| `GET` | `/admin/forgot-password` | `PasswordResetLinkController@create` | `password.request` |
| `POST` | `/admin/forgot-password` | `PasswordResetLinkController@store` | `password.email` |
| `GET` | `/admin/reset-password/{token}` | `NewPasswordController@create` | `password.reset` |
| `POST` | `/admin/reset-password` | `NewPasswordController@store` | `password.update` |
| `GET` | `/admin/two-factor-challenge` | `TwoFactorAuthenticatedSessionController@create` | `two-factor.login` |
| `POST` | `/admin/two-factor-challenge` | `TwoFactorAuthenticatedSessionController@store` | `two-factor.login.attempt` |
| `GET` | `/admin/email/verify` | `EmailVerificationPromptController` | `verification.notice` |
| `GET` | `/login-test` | `Frontend\AuthTestController@login` | `login-test` |

### Controllers

- `App\Http\Controllers\RegisteredUserController`
- `App\Http\Controllers\NewPasswordController`
- `App\Http\Controllers\PasswordResetLinkController`
- `App\Http\Controllers\TwoFactorAuthenticatedSessionController`
- `App\Http\Controllers\VerifyEmailController`
- `App\Http\Controllers\CustomOAuthController`
- `App\Http\Controllers\Frontend\AuthTestController`
- `Laravel\Fortify\Http\Controllers\AuthenticatedSessionController`
- `Laravel\Fortify\Http\Controllers\EmailVerificationPromptController`

### Middleware

- `guest:{guard}` — Redirects authenticated users away from guest-only pages
- `recaptcha` — reCAPTCHA validation on POST login/2FA
- `recaptchaRegisterPage` — reCAPTCHA on registration
- `recaptchaForgotPasswordPage` — reCAPTCHA on forgot password
- `recaptchaAdminLoginPage` — reCAPTCHA on admin login
- `ensureLoginFromLoginRoute` — Enforces proper login flow for frontend
- `ensureLoginFromAdminLoginRoute` — Enforces proper login flow for admin panel
- `throttle:{limiter}` — Rate limiting on login/2FA/forgot-password
- `signed` — Signed URL validation for email verification

### Jobs

- `App\Jobs\RemoveNotVerifiedUser` — Scheduled daily; removes users who never verified their email
- `App\Jobs\SendResetPasswordLink` — Dispatched to queue the password reset email

---

## Feature 2 — User Management

**Description:** Admin CRUD for users, roles, and password management. Includes user suspension, soft deletes, trashed record recovery, reassignment, and password reset email dispatch.

### Routes (`routes/admin.php`)

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET` | `/admin/users` | `UserController@index` | `users.index` |
| `GET` | `/admin/users/create` | `UserController@create` | `users.create` |
| `POST` | `/admin/users` | `UserController@store` | `users.store` |
| `GET` | `/admin/users/{user}/edit` | `UserController@edit` | `users.edit` |
| `PUT` | `/admin/users/{user}` | `UserController@update` | `users.update` |
| `DELETE` | `/admin/users/{user}` | `UserController@destroy` | `users.destroy` |
| `GET` | `/admin/users/trashed-records` | `UserController@getTrashedRecords` | `users.trashed-records` |
| `PUT` | `/admin/users/{user}/password` | `UserController@updatePassword` | `users.password` |
| `GET` | `/admin/users/reassignment-candidates/{user}` | `UserController@getReassignmentCandidates` | `users.reassignment-candidates` |
| `POST` | `/admin/users/suspend/{user}` | `UserController@suspend` | `users.suspend` |
| `POST` | `/admin/users/unsuspend/{user}` | `UserController@unsuspend` | `users.unsuspend` |
| `GET` | `/admin/users/password-reset/form-data` | `SendUserPasswordResetEmailController@passwordResetFormData` | `users.password-reset.form-data` |
| `POST` | `/admin/users/password-reset/send` | `SendUserPasswordResetEmailController` | `users.password-reset.send` |
| `GET` | `/admin/roles` | `RoleController@index` | `roles.index` |
| `POST` | `/admin/roles` | `RoleController@store` | `roles.store` |
| `PUT` | `/admin/roles/{role}` | `RoleController@update` | `roles.update` |
| `DELETE` | `/admin/roles/{role}` | `RoleController@destroy` | `roles.destroy` |
| `GET` | `/admin/api/users/{user}/cities` | `Api\CityUserController@index` | `api.users.cities.index` |
| `POST` | `/admin/api/users/{user}/cities` | `Api\CityUserController@update` | `api.users.cities.update` |

### Controllers

- `App\Http\Controllers\UserController`
- `App\Http\Controllers\RoleController`
- `App\Http\Controllers\SendUserPasswordResetEmailController`
- `App\Http\Controllers\UserProfileController`
- `App\Http\Controllers\UserPasswordController`
- `App\Http\Controllers\Api\CityUserController`

### Middleware

- `can:manageUserTrashed,App\Models\User`
- `can:managePasswordResetEmail,App\Models\User`
- `CheckSuspended` (global web middleware) — blocks suspended users from all routes

---

## Feature 3 — Frontend / Public Pages

**Description:** Public-facing website routes. Serves the homepage, blog posts, post categories, and dynamic CMS pages through a page builder. Also includes public user profiles, QR code printing, and SEO-friendly sitemaps.

### Routes (`routes/web.php`)

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET` | `/{locale}/` | `Frontend\PageController@homePage` | `homepage` |
| `GET` | `/{locale}/{page_translation}` | `Frontend\PageController@show` | `frontend.pages.show` |
| `GET` | `/{locale}/blog` | `Frontend\PostController@index` | `blog.index` |
| `GET` | `/{locale}/blog/{slug}` | `Frontend\PostController@show` | `blog.show` |
| `GET` | `/{locale}/blog/category/{category}` | `Frontend\PostCategoryController@index` | `blog.category.index` |
| `GET` | `/{locale}/{username}` | `Frontend\ProfileController@show` | `frontend.profile` |
| `GET` | `/print/qrcode/{user}` | `Frontend\QrCodeController@print` | `frontend.print.qrcode` |
| `GET` | `/{locale}/sitemap_index.xml` | `SitemapController@sitemaps` | `sitemap` |
| `GET` | `/{locale}/{name}-sitemap.xml` | `SitemapController@urls` | `sitemap.urls` |
| `GET` | `/language/{new_locale}` | `ChangeLanguageController` | `language.change` |
| `GET` | `css/pb-{uid}.css` | `StylePageBuilderController` | `page.css` |
| `GET` | `css/stored/{css_name}` | `StoredCssController` | `css.stored` |

### Controllers

- `App\Http\Controllers\Frontend\PageController`
- `App\Http\Controllers\Frontend\PostController`
- `App\Http\Controllers\Frontend\PostCategoryController`
- `App\Http\Controllers\Frontend\ProfileController`
- `App\Http\Controllers\Frontend\QrCodeController`
- `App\Http\Controllers\SitemapController`
- `App\Http\Controllers\ChangeLanguageController`
- `App\Http\Controllers\StylePageBuilderController`
- `App\Http\Controllers\StoredCssController`

### Middleware

- `localizationRedirect` — Enforces locale prefix in URL
- `adjustOriginLanguage` — Normalizes language origin for content queries
- `redirectOriginLanguage` — Redirects root to localized homepage
- `publicPage:profile` — Checks if profile page is publicly accessible
- `changeLanguage` — Persists language switch to session/cookie

---

## Feature 4 — Admin CMS (Pages, Posts, Categories, Media)

**Description:** Admin CRUD for content management — pages with page builder, blog posts, post categories, and media library with image editing and replacement capabilities.

### Routes (`routes/admin.php`)

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET/POST/PUT/DELETE` | `/admin/pages` | `PageController` (resource) | `pages.*` |
| `POST` | `/admin/pages/duplicate/{page}` | `PageController@duplicatePage` | `pages.duplicate` |
| `DELETE` | `/admin/pages/translations/{id}/destroy` | `PageController@translationDestroy` | `pages.translations.destroy` |
| `GET/POST/DELETE` | `/admin/media` | `MediaController` (resource) | `media.*` |
| `POST` | `/admin/media/update-image/{medium}` | `MediaController@updateImage` | `media.update-image` |
| `POST` | `/admin/media/save-as-image/{medium}` | `MediaController@saveAsImage` | `media.save-as-image` |
| `GET` | `/admin/media-lists` | `MediaController@lists` | `media.lists` |
| `GET/POST/PUT/DELETE` | `/admin/categories` | `CategoryController` (resource) | `categories.*` |
| `GET/POST/PUT/DELETE` | `/admin/posts` | `PostController` (resource) | `posts.*` |
| `POST` | `/admin/api/media` | `MediaController@apiStore` | `api.media.store` |
| `POST` | `/admin/api/media/replace` | `MediaController@apiReplace` | `api.media.replace` |
| `GET` | `/admin/api/pages/{page}/is-used-by-menu/{locale?}` | `PageController@isUsedByMenus` | `api.pages.is-used-by-menu` |
| `GET` | `/admin/api/page-builder/type-options` | `ApiPageBuilderController@typeOptions` | `api.page-builder.type-options` |
| `GET` | `/admin/api/page-builder/user-list/role-options` | `ApiPageBuilderController@userListRoleOptions` | `api.page-builder.user-list.role-options` |
| `GET` | `/admin/api/page-builder/post/category-options` | `ApiPageBuilderController@postCategoryOptions` | `api.page-builder.post.category-options` |
| `GET` | `/admin/api/tinymce/key` | `SettingKeyController@getTinyMCEKey` | `api.tinymce.key` |

### Controllers

- `App\Http\Controllers\PageController`
- `App\Http\Controllers\PostController`
- `App\Http\Controllers\CategoryController`
- `App\Http\Controllers\MediaController`
- `App\Http\Controllers\ApiPageBuilderController`
- `App\Http\Controllers\ApiPageBuilderComponentUserListController`

---

## Feature 5 — Dashboard & Admin Widgets

**Description:** Admin dashboard landing page and widget data API endpoints for real-time stats panels (latest registrations, bookings, form entries).

### Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET` | `/admin/dashboard` | `DashboardController@index` | `dashboard` |
| `GET` | `/dashboard` | `Frontend\DashboardController@index` | `dashboard` |
| `GET` | `/admin/api/widget/latest-registrations` | `ApiWidgetController@getLatestRegistrations` | `api.widget.latest-registrations` |
| `GET` | `/admin/api/widget/data/{uuid}` | `ApiWidgetController@getStoredWidgetData` | `api.widget.data` |
| `GET` | `/admin/api/booking/widget/latest-bookings` | `Booking\ApiWidgetController@getLatestBookings` | `api.booking.widget.latest-bookings` |
| `GET` | `/admin/api/form-builder/widget/form-builder/{form}/entries` | `FormBuilder\ApiWidgetController@getEntries` | `api.widget.form-builder.entries` |

### Controllers

- `App\Http\Controllers\DashboardController` (admin)
- `App\Http\Controllers\Frontend\DashboardController` (frontend)
- `App\Http\Controllers\ApiWidgetController`
- `Modules\Booking\Http\Controllers\ApiWidgetController`
- `Modules\FormBuilder\Http\Controllers\ApiWidgetController`

---

## Feature 6 — Theme & Appearance Settings

**Description:** Admin panel controls for visual customization — colors, typography, header/footer layouts, navigation menus, SEO settings, and advanced CSS overrides.

### Routes (`routes/admin.php` under prefix `theme`)

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET/POST` | `/admin/theme/color` | `ThemeColorController` | `theme.color.*` |
| `GET/POST` | `/admin/theme/header` | `ThemeHeaderController` | `theme.header.*` |
| `POST` | `/admin/theme/header/navigation` | `ThemeHeaderNavigationController@update` | `theme.header.navigation.update` |
| `POST` | `/admin/api/theme/header/menu-item` | `ThemeHeaderNavigationController@apiValidateMenuItem` | `api.theme.header.menu-item.validate` |
| `GET/POST` | `/admin/theme/footer` | `ThemeFooterController` | `theme.footer.*` |
| `POST` | `/admin/theme/footer/menu-item` | `ThemeFooterNavigationController@update` | `theme.footer.navigation.update` |
| `POST` | `/admin/api/theme/footer/social-media` | `ThemeFooterController@apiValidateSocialMedia` | `api.theme.footer.social-media.validate` |
| `GET/POST` | `/admin/theme/advance` | `ThemeAdvanceController` | `theme.advance.*` |
| `GET/POST` | `/admin/theme/fonts` | `ThemeFontController` | `theme.fonts.*` |
| `GET/POST` | `/admin/theme/seo` | `ThemeSeoController` | `theme.seo.*` |

### Controllers

- `App\Http\Controllers\ThemeColorController`
- `App\Http\Controllers\ThemeHeaderController`
- `App\Http\Controllers\ThemeHeaderNavigationController`
- `App\Http\Controllers\ThemeFooterController`
- `App\Http\Controllers\ThemeFooterNavigationController`
- `App\Http\Controllers\ThemeAdvanceController`
- `App\Http\Controllers\ThemeFontController`
- `App\Http\Controllers\ThemeSeoController`

### Jobs (triggered on theme changes)

- `App\Jobs\CompileThemeCss` — Recompiles theme CSS after settings update
- `App\Jobs\UpdateStripeConnectedAccountBrandingLogo` — Syncs branding to Stripe
- `App\Jobs\UpdateStripeConnectedAccountColor` — Syncs colors to Stripe

---

## Feature 7 — System Settings

**Description:** Admin panel settings covering language management, translation manager (import/export), API keys (TinyMCE, etc.), cookie consent, Stripe global config, and module management.

### Routes (`routes/admin.php` under prefix `settings`)

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET/POST` | `/admin/settings/languages` | `LanguageController` | `settings.languages.*` |
| `GET/POST/DELETE` | `/admin/settings/translation-manager` | `TranslationManagerController` | `settings.translation-manager.*` |
| `GET/POST` | `/admin/settings/stripe` | `StripeController` | `settings.stripe.*` |
| `GET/POST` | `/admin/settings/keys` | `SettingKeyController` | `settings.keys.*` |
| `GET/POST` | `/admin/settings/cookie-consent` | `CookieConsentController` | `settings.cookie-consent.*` |
| `GET/POST/PUT` | `/admin/settings/modules` | `ModuleController` | `settings.modules.*` |
| `GET/POST` | `/admin/api/options/phone-countries` | `ApiOptionController@phoneCountryOptions` | `api.options.phone-countries` |
| `GET` | `/admin/api/options/countries` | `ApiOptionController@countryOptions` | `api.options.countries` |
| `GET` | `/admin/api/options/timezones` | `ApiOptionController@timezoneOptions` | `api.options.timezones` |
| `GET` | `/admin/api/setting/max-file-size` | `ApiSettingController@maxFileSize` | `api.setting.max-file-size` |
| `GET` | `/admin/api/cities` | `Api\CityController@index` | `api.cities.index` |

### Controllers

- `App\Http\Controllers\LanguageController`
- `App\Http\Controllers\TranslationManagerController`
- `App\Http\Controllers\StripeController` (admin settings)
- `App\Http\Controllers\SettingKeyController`
- `App\Http\Controllers\CookieConsentController`
- `App\Http\Controllers\ModuleController`
- `App\Http\Controllers\ApiOptionController`
- `App\Http\Controllers\ApiSettingController`
- `App\Http\Controllers\Api\CityController`

### Events + Listeners (Module lifecycle)

| Event | Listener | Effect |
|-------|----------|--------|
| `App\Events\ModuleDeactivated` | `App\Listeners\SanitizeDisabledComponentsOnPageTranslations` | Removes disabled module components from page content |
| `App\Events\ModuleDeactivated` | `App\Listeners\UnassignModulePermissions` | Strips permissions tied to the deactivated module |

---

## Feature 8 — Logging & Error Monitoring

**Description:** Admin system activity log and error log management. Errors can be dispatched as notifications (e.g., Telegram). Telescope integration is available in non-production environments.

### Routes (`routes/admin.php`)

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET` | `/admin/system-log` | `SystemLogController@index` | `system-log.index` |
| `GET` | `/admin/system-log/search-users` | `SystemLogController@searchUsers` | `system-log.search-users` |
| `GET` | `/admin/error-log` | `ErrorLogController@index` | `error-log.index` |
| `DELETE` | `/admin/error-log/all` | `ErrorLogController@destroyAll` | `error-log.destroy.all` |
| `DELETE` | `/admin/error-log/{errorLog}` | `ErrorLogController@destroy` | `error-log.destroy` |
| `POST` | `/admin/error-log/delete-checked` | `ErrorLogController@destroyChecked` | `error-log.destroy.checked` |

### Controllers

- `App\Http\Controllers\SystemLogController`
- `App\Http\Controllers\ErrorLogController`

### Events + Listeners

| Event | Listener | Schedule |
|-------|----------|----------|
| `App\Events\ErrorReport` | `App\Listeners\SendErrorReportNotification` | Dispatched daily at 23:59 via scheduler |

---

## Feature 9 — Payments & Stripe Integration

**Description:** Stripe connected accounts for performers — creation, onboarding, dashboard linking, and settings. Includes Stripe webhook handling and admin-level Stripe configuration.

### Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET` | `/payments` | `Frontend\PaymentController@index` | `payments.index` |
| `GET` | `/payments/stripe` | `Frontend\StripeController@show` | `payments.stripe.show` |
| `POST` | `/payments/stripe/create-connected-account` | `Frontend\StripeController@createThenRedirect` | `payments.stripe.create-connected-account` |
| `POST` | `/payments/stripe/update-setting` | `Frontend\StripeController@updateSetting` | `payments.stripe.update-setting` |
| `GET` | `/payments/stripe/redirect-to-stripe` | `Frontend\StripeController@redirectToStripeAccount` | `payments.stripe.redirect-to-stripe` |
| `GET` | `/payments/stripe/account-link` | `Frontend\StripeController@accountLink` | `payments.stripe.account-link` |
| `GET` | `/payments/stripe/reauth` | `Frontend\StripeController@refresh` | `payments.stripe.refresh` |
| `GET` | `/payments/stripe/return` | `Frontend\StripeController@return` | `payments.stripe.return` |
| `POST` | `/webhooks/stripe` | `WebhookStripeController` | — |

### Controllers

- `App\Http\Controllers\Frontend\PaymentController`
- `App\Http\Controllers\Frontend\StripeController`
- `App\Http\Controllers\WebhookStripeController`
- `App\Http\Controllers\StripeController` (admin settings)

### Jobs

- `App\Jobs\UpdateStripeConnectedAccountBrandingLogo` — Updates Stripe account logo on theme change
- `App\Jobs\UpdateStripeConnectedAccountColor` — Updates Stripe account accent color on theme change

### Middleware

- `can:manageStripeConnectedAccount,App\Models\User` — Gate on all `/payments/stripe/*` routes

---

## Feature 10 — Donations

**Description:** Public donation flow allowing visitors to donate to a specific user via Stripe Checkout.

### Routes (`routes/web.php`)

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET` | `/{locale}/donations/{user}/success` | `Frontend\DonationController@success` | `donations.success` |
| `POST` | `/{locale}/donations/checkout/{user}` | `Frontend\DonationController@checkout` | `donations.checkout` |

### Controllers

- `App\Http\Controllers\Frontend\DonationController`

### Middleware

- `throttle:checkout` — Rate limiting on checkout POST

---

## Feature 11 — Booking (Module)

**Description:** Full booking lifecycle — products (services/events), event scheduling with recurrence rules, order management, rescheduling, cancellation, and check-in. Covers both admin management and the authenticated performer-facing frontend.

### Routes (`modules/Booking/Routes/web.php`)

**Admin (`/admin/booking`):**

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| CRUD | `/admin/booking/products` | `ProductController` | `admin.booking.products.*` |
| `PUT` | `/admin/booking/products/{product}/event` | `ProductEventController@update` | `admin.booking.products.events.update` |
| CRUD | `/admin/booking/products/{product}/product-events` | `ProductEventCrudController` | `admin.booking.products.product-events.*` |
| `GET` | `/admin/booking/products/{product}/product-event-records` | `ProductEventCrudController@records` | `admin.booking.products.product-events.records` |
| `GET/PUT` | `/admin/booking/products/{product}/product-events/{event}/schedule` | `ProductEventScheduleController` | `admin.booking.products.product-events.schedule.*` |
| `GET` | `/admin/booking/products/{product}/allowed-dates/{month}/{year}` | `FrontendProductController@allowedDates` | `admin.booking.products.allowed-dates` |
| `PUT` | `/admin/booking/products/{product}/space` | `ProductSpaceController@update` | `admin.booking.products.spaces.update` |
| `GET/POST` | `/admin/booking/orders` | `OrderController` | `admin.booking.orders.*` |
| `POST` | `/admin/booking/orders/{order}/cancel` | `OrderController@cancel` | `admin.booking.orders.cancel` |
| `GET/POST` | `/admin/booking/orders/{order}/reschedule` | `OrderController@reschedule` | `admin.booking.orders.reschedule.*` |
| `GET` | `/admin/booking/orders/{order}/available-times/{date}` | `OrderController@availableTimes` | `admin.booking.orders.available-times` |
| `GET/POST` | `/admin/booking/settings` | `SettingController` | `admin.booking.settings.*` |

**Frontend (`/booking`):**

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET` | `/booking/products` | `Frontend\ProductController@index` | `booking.products.index` |
| `GET` | `/booking/products/{product}` | `Frontend\ProductController@show` | `booking.products.show` |
| `GET` | `/booking/products/{product}/available-times/{date}` | `Frontend\ProductController@availableTimes` | `booking.products.available-times` |
| `GET` | `/booking/products/{product}/allowed-dates/{month}/{year}` | `Frontend\ProductController@allowedDates` | `booking.products.allowed-dates` |
| `GET` | `/booking/events/{event}` | `Frontend\EventController@show` | `booking.events.show` |
| `GET` | `/booking/events/{event}/available-times/{date}` | `Frontend\EventController@availableTimes` | `booking.events.available-times` |
| `GET/POST` | `/booking/orders` | `Frontend\OrderController` | `booking.orders.*` |
| `GET/POST` | `/booking/orders/{order}/reschedule` | `Frontend\OrderController@reschedule` | `booking.orders.reschedule.*` |
| `POST` | `/booking/orders/{order}/cancel` | `Frontend\OrderController@cancel` | `booking.orders.cancel` |
| `POST` | `/booking/orders/{order}/check-in` | `Frontend\CheckInController` | `booking.orders.check-in` |
| `POST` | `/booking/orders/{product}/book-event` | `Frontend\OrderController@bookEvent` | `booking.orders.book-event` |
| `POST` | `/booking/orders/{product}/book-event-batch` | `Frontend\OrderController@bookEventBatch` | `booking.orders.book-event-batch` |

**API (`/api/booking`):**

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET` | `/api/booking/events-calendar` | `ApiPageBuilderComponent\EventsCalendarController@index` | `api.booking.events-calendar.index` |
| `GET` | `/api/booking/events-calendar/location-options` | `EventsCalendarController@getLocationOptions` | `api.booking.events-calendar.location-options` |
| `GET` | `/api/booking/widget/latest-bookings` | `ApiWidgetController@getLatestBookings` | `api.booking.widget.latest-bookings` |
| `GET` | `/api/booking/upcoming-events/{userUniqueKey}` | `UpcomingEventController@events` | `api.booking.upcoming-events` |

### Controllers

- `Modules\Booking\Http\Controllers\ProductController`
- `Modules\Booking\Http\Controllers\ProductEventController`
- `Modules\Booking\Http\Controllers\ProductEventCrudController`
- `Modules\Booking\Http\Controllers\ProductEventScheduleController`
- `Modules\Booking\Http\Controllers\ProductSpaceController`
- `Modules\Booking\Http\Controllers\OrderController`
- `Modules\Booking\Http\Controllers\SettingController`
- `Modules\Booking\Http\Controllers\ApiWidgetController`
- `Modules\Booking\Http\Controllers\Frontend\ProductController`
- `Modules\Booking\Http\Controllers\Frontend\EventController`
- `Modules\Booking\Http\Controllers\Frontend\OrderController`
- `Modules\Booking\Http\Controllers\Frontend\CheckInController`
- `Modules\Booking\Http\Controllers\Frontend\UpcomingEventController`
- `Modules\Booking\Http\Controllers\ApiPageBuilderComponent\EventsCalendarController`

### Events + Listeners

| Event | Listeners |
|-------|-----------|
| `Booking\Events\EventBooked` | `SendBookedEventNotification` |
| `Booking\Events\EventCanceled` | `SendCanceledEventNotification`, `CancelUpcomingOrOngoingBookings` |
| `Booking\Events\EventRescheduled` | `SendRescheduledEventNotification` |
| `Booking\Events\ModuleDeactivated` | `SetPublishedProductsToDraft`, `UnassignAllProductManagers`, `UnassignSpaceFromProduct` |

### Middleware

- `verifyModule:Booking` — Blocks access if module is disabled

---

## Feature 12 — Spaces (Module)

**Description:** Venue/space management — admins manage spaces, space types, and events tied to spaces. Frontend shows a public spaces directory. Integrates with Booking module for space-linked products.

### Routes (`modules/Space/Routes/web.php`)

**Admin (`/admin/spaces`):**

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| CRUD | `/admin/spaces` | `SpaceController` | `admin.spaces.*` |
| `POST` | `/admin/spaces/update-manager/{space}` | `SpaceController@updateManagers` | `admin.spaces.update-managers` |
| `GET` | `/admin/spaces/search-managers` | `SpaceController@searchManagers` | `admin.spaces.search-managers` |
| `GET` | `/admin/spaces/settings` | `SettingController@index` | `admin.spaces.settings.index` |
| CRUD | `/admin/spaces/settings/space-types` | `SpaceTypeController` | `admin.spaces.settings.space-types.*` |
| CRUD | `/admin/spaces/{space}/pages` | `PageController` | `admin.spaces.pages.*` |
| CRUD | `/admin/spaces/{space}/events` | `EventController` | `admin.spaces.events.*` |
| `GET` | `/admin/spaces/{space}/event-records` | `EventController@records` | `admin.spaces.events.records` |
| `POST` | `/admin/api/spaces/contact` | `ContactController@apiValidateContact` | `admin.api.spaces.contact.validate` |
| `GET` | `/admin/api/spaces/{space}/is-used-by-menu/{locale?}` | `SpaceController@isUsedByMenus` | `admin.api.spaces.is-used-by-menu` |

**Frontend:**

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET` | `/{locale}/spaces` | `Frontend\SpaceController@index` | `frontend.spaces.index` |
| `GET` | `/{locale}/spaces/{slugs}` | `Frontend\SpaceController@show` | `frontend.spaces.show` |
| `GET` | `/api/space/space-events/{encryptedSpaceId}` | `Frontend\SpaceEventController@events` | `api.space.space-events` |

### Controllers

- `Modules\Space\Http\Controllers\SpaceController`
- `Modules\Space\Http\Controllers\EventController`
- `Modules\Space\Http\Controllers\PageController`
- `Modules\Space\Http\Controllers\SettingController`
- `Modules\Space\Http\Controllers\SpaceTypeController`
- `Modules\Space\Http\Controllers\ContactController`
- `Modules\Space\Http\Controllers\Frontend\SpaceController`
- `Modules\Space\Http\Controllers\Frontend\SpaceEventController`

### Middleware

- `CanManageEvent` — Restricts space event management to authorized users
- `verifyModule:Space` — Blocks access if module is disabled
- `redirectIfModuleIsDisabled:Space` — Redirects frontend visitors if module is off

### Events + Listeners

| Event | Listeners |
|-------|-----------|
| `Space\Events\ModuleDeactivated` | `RemoveSpaceFromMenus`, `SetPublishedEventsDrafts`, `SetPublishedPageTranslationsDrafts`, `UnassignAllSpaceManagers`, `UnassignSpaceFromProduct` |

---

## Feature 13 — Form Builder (Module)

**Description:** Drag-and-drop form builder for creating contact/lead capture forms. Supports form entries (inbox), bulk actions, email notifications, and an optional "automate user creation" flow that creates/updates users from form submissions.

### Routes (`modules/FormBuilder/Routes/web.php`)

**Admin (`/admin/form-builders`):**

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| CRUD | `/admin/form-builders` | `FormBuilderController` | `admin.form-builders.*` |
| `GET` | `/admin/form-builders/{form}/entries` | `FormEntryController@index` | `admin.form-builders.entries.index` |
| `GET` | `/admin/form-builders/{form}/entries/{entry}` | `FormEntryController@show` | `admin.form-builders.entries.show` |
| `POST` | `/admin/form-builders/{form}/entries/bulk-mark-as-read` | `FormEntryController@bulkMarkAsRead` | `admin.form-builders.entries.bulk-mark-as-read` |
| `POST` | `/admin/form-builders/{form}/entries/bulk-archive` | `FormEntryController@bulkArchive` | `admin.form-builders.entries.bulk-archive` |
| `POST` | `/admin/form-builders/{form}/entries/bulk-restore` | `FormEntryController@bulkRestore` | `admin.form-builders.entries.bulk-restore` |
| `POST` | `/admin/form-builders/{form}/entries/bulk-force-delete` | `FormEntryController@bulkForceDelete` | `admin.form-builders.entries.bulk-force-delete` |
| `POST` | `/admin/form-builders/{form}/automate-user-creation/mapped-fields` | `AutomateUserCreationController@save` | `admin.form-builders.automate-user-creation.mapped-fields.save` |
| `POST` | `/admin/form-builders/{form}/entries/{entry}/create-or-update-user` | `AutomateUserCreationController@createOrUpdateUser` | `admin.form-builders.entries.automate-user-creation.create-or-update` |
| CRUD | `/admin/form-builders/{form}/settings/notifications` | `SettingNotificationController` | `admin.form-builders.settings.notifications.*` |
| `PUT` | `/admin/form-builders/{form}/settings/general/update` | `SettingController@update` | `admin.form-builders.settings.general.update` |
| `GET` | `/admin/api/page-builders/form-options` | `PageBuilderController@formOptions` | `admin.api.page-builders.form-options` |
| `GET` | `/admin/api/widget/form-builder/{form}/entries` | `ApiWidgetController@getEntries` | `admin.api.widget.form-builder.entries` |
| `GET` | `/admin/api/automate-user-creation/{entry}/confirmation` | `AutomateUserCreationController@confirmation` | `admin.api.automate-user-creation.confirmation` |

**Public:**

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET` | `/form-builders/schema` | `FormBuilderController@getSchema` | `form-builders.schema` |
| `POST` | `/form-builders/save` | `FormBuilderController@submit` | `form-builders.save` |

### Controllers

- `Modules\FormBuilder\Http\Controllers\FormBuilderController`
- `Modules\FormBuilder\Http\Controllers\FormEntryController`
- `Modules\FormBuilder\Http\Controllers\AutomateUserCreationController`
- `Modules\FormBuilder\Http\Controllers\SettingController`
- `Modules\FormBuilder\Http\Controllers\SettingNotificationController`
- `Modules\FormBuilder\Http\Controllers\PageBuilderController`
- `Modules\FormBuilder\Http\Controllers\ApiWidgetController`

### Events + Listeners

| Event | Listeners |
|-------|-----------|
| `FormBuilder\Events\FormSubmitted` | `SendFormNotification` (sends email to configured recipients) |
| `FormBuilder\Events\ModuleDeactivated` | `DeactivateAllNotificationSettings` |

### Middleware

- `verifyModule:FormBuilder` — Blocks access if module is disabled
- `recaptcha` + `throttle:defaultRequest` — On public form submission

---

## Feature 14 — Legacy/Core Forms

**Description:** Legacy form submission endpoint (pre-FormBuilder module), used for embedded shortcode forms in page content.

### Routes (`routes/web.php`)

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| `GET` | `/forms/schemas` | `FormController@getSchemas` | `forms.schemas` |
| `POST` | `/forms/save` | `FormController@submit` | `forms.save` |

### Controllers

- `App\Http\Controllers\FormController`

---

## Middleware Registry

### Global Middleware (every request)

| Middleware | Class |
|------------|-------|
| `TrustProxies` | `App\Http\Middleware\TrustProxies` |
| `HandleCors` | `Illuminate\Http\Middleware\HandleCors` |
| `PreventRequestsDuringMaintenance` | `App\Http\Middleware\PreventRequestsDuringMaintenance` |
| `ValidatePostSize` | `Illuminate\Foundation\Http\Middleware\ValidatePostSize` |
| `TrimStrings` | `App\Http\Middleware\TrimStrings` |
| `ConvertEmptyStringsToNull` | `Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull` |

### Web Group Middleware

| Middleware | Class |
|------------|-------|
| `EncryptCookies` | `App\Http\Middleware\EncryptCookies` |
| `AddQueuedCookiesToResponse` | `Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse` |
| `StartSession` | `Illuminate\Session\Middleware\StartSession` |
| `ShareErrorsFromSession` | `Illuminate\View\Middleware\ShareErrorsFromSession` |
| `VerifyCsrfToken` | `App\Http\Middleware\VerifyCsrfToken` |
| `SubstituteBindings` | `Illuminate\Routing\Middleware\SubstituteBindings` |
| `HandleInertiaRequests` | `App\Http\Middleware\HandleInertiaRequests` |
| `CheckSuspended` | `App\Http\Middleware\CheckSuspended` |

### Named Route Middleware

| Alias | Class | Purpose |
|-------|-------|---------|
| `auth` | `App\Http\Middleware\Authenticate` | Require authenticated user |
| `guest` | `App\Http\Middleware\RedirectIfAuthenticated` | Redirect authenticated away |
| `verified` | `App\Http\Middleware\UserEmailIsVerified` | Require verified email |
| `setClientAuthToken` | `App\Http\Middleware\SetClientAuthToken` | Attach auth token for admin API calls |
| `ensureLoginFromAdminLoginRoute` | `App\Http\Middleware\EnsureLoginFromAdminLoginRoute` | Enforce admin login entry |
| `ensureLoginFromLoginRoute` | `App\Http\Middleware\EnsureLoginFromLoginRoute` | Enforce frontend login entry |
| `publicPage` | `App\Http\Middleware\PublicPageIsAvailable` | Check if public page is accessible |
| `recaptcha` | `App\Http\Middleware\Recaptcha` | Generic reCAPTCHA check |
| `recaptchaRegisterPage` | `App\Http\Middleware\RecaptchaRegisterPage` | reCAPTCHA on register |
| `recaptchaForgotPasswordPage` | `App\Http\Middleware\RecaptchaForgotPasswordPage` | reCAPTCHA on forgot password |
| `recaptchaAdminLoginPage` | `App\Http\Middleware\RecaptchaAdminLoginPage` | reCAPTCHA on admin login |
| `redirectOriginLanguage` | `App\Http\Middleware\RedirectOriginLanguage` | Redirect root to locale homepage |
| `localizationRedirect` | `Mcamara\LaravelLocalization\Middleware\...` | Enforce locale prefix |
| `adjustOriginLanguage` | `App\Http\Middleware\AdjustOriginLanguage` | Normalize language origin |
| `changeLanguage` | `App\Http\Middleware\ChangeLanguage` | Persist language change |
| `role` | `Spatie\Permission\Middlewares\RoleMiddleware` | Role-based access |
| `permission` | `Spatie\Permission\Middlewares\PermissionMiddleware` | Permission-based access |
| `verifyModule` | `App\Http\Middleware\VerifyModule` | Block if module disabled |
| `redirectIfModuleIsDisabled` | `App\Http\Middleware\RedirectIfModuleIsDisabled` | Frontend redirect if module off |
| `CanManageEvent` (Space) | `Modules\Space\Http\Middleware\CanManageEvent` | Space event management gate |

---

## Jobs Registry

| Job | Trigger | Schedule |
|-----|---------|----------|
| `RemoveNotVerifiedUser` | Scheduler | Daily |
| `SendResetPasswordLink` | Queued on password reset | On demand |
| `DeleteMediaFromStorage` | Queued on media delete | On demand |
| `ProcessPublishScheduledPost` | Queued on post publish | On demand |
| `CompileThemeCss` | Queued on theme update | On demand |
| `GeneratePageStyle` (command) | Artisan command | Manual |
| `UpdateStripeConnectedAccountBrandingLogo` | Queued on logo change | On demand |
| `UpdateStripeConnectedAccountColor` | Queued on color change | On demand |

---

## Scheduled Tasks (Console Kernel)

| Task | Schedule |
|------|----------|
| `telescope:prune` | Daily (48h local / 336h prod) |
| `ErrorReport::dispatch()` | Daily at 23:59 |
| `RemoveNotVerifiedUser` job | Daily |
| `auth:clear-resets` | Every 15 minutes |

---

## Artisan Commands (Custom)

Located in `app/Console/Commands/`:

| Command Class | Purpose |
|---------------|---------|
| `FixBookProductWidgetUrl` | Data fix — book product widget URLs |
| `FixCoverDimension` | Data fix — cover image dimensions |
| `FixMediaStructure` | Data fix — media storage structure |
| `FixTranslationRoute` / `FixTranslationSource` / `FixTranslationsModule` | Translation data migrations |
| `GeneratePageStyle` | Generate per-page CSS |
| `GenerateThemeCss` | Recompile full theme CSS |
| `ImportTranslations` | Import translation files |
| `MigrateCityData` | Migrate city data |
| `ProdPreparationRemoveCloudinary` | Pre-prod cleanup: Cloudinary data |
| `ProdPreparationRemoveUnusedData` | Pre-prod cleanup: unused DB records |
| `ProdPreparationRemoveUser` | Pre-prod cleanup: test users |
| `ProdPreparationReplaceUrl` | Pre-prod: replace URLs |
| `ProdPreparationUserPerformer` | Pre-prod: seed performer users |
| `SendTrialCronTelegramMessage` | Send Telegram notification (trial cron test) |
| `RenderThemeSass` | Render/compile theme SASS |

---

*End of Entry Points Map*
