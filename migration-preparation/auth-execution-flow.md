# Authentication & Session Management — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — full trace of every auth flow from HTTP route to database to response, including hidden dependencies, side effects, and key logic decisions.

---

## Overview: Dual-Portal Architecture

This application runs **two completely separate login contexts** on the same codebase:

| Portal | Entry Route | Home After Login | Guard |
|--------|-------------|-----------------|-------|
| **Frontend** (performers) | `POST /login` | `config('fortify.home')` (`/dashboard`) | `web` |
| **Admin Panel** | `POST /admin/login` | `config('fortify.admin_home')` (`/admin/dashboard`) | `web` |

A session key `home_url` acts as the runtime "memory" of which portal the user logged in from. This key unlocks or blocks access to protected routes via two custom middleware: `EnsureLoginFromLoginRoute` and `EnsureLoginFromAdminLoginRoute`.

> ⚠️ **Critical coupling**: If the session is lost (e.g. after a server restart or session driver change), users are silently redirected to their `home_url`, which defaults to `null` → they may get stuck in redirect loops.

---

## Flow 1 — Frontend Login (`POST /login`)

### 1. Route Entry

```
routes/web.php
  Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware([
        'guest:web',
        'throttle:{loginLimiter}',
        'recaptcha',
    ]);
```

**Middleware chain (in execution order):**

```
GlobalStack → web group → route-specific middleware
```

| # | Middleware | Location | Purpose |
|---|-----------|----------|---------|
| 1 | `TrustProxies` | Global | Sets trusted proxy headers (for Heroku/load balancer) |
| 2 | `HandleCors` | Global | CORS header injection |
| 3 | `PreventRequestsDuringMaintenance` | Global | 503 if maintenance mode |
| 4 | `ValidatePostSize` | Global | Rejects oversized POST bodies |
| 5 | `TrimStrings` | Global | Trims whitespace from all string inputs |
| 6 | `ConvertEmptyStringsToNull` | Global | Normalizes empty strings to null |
| 7 | `EncryptCookies` | Web group | Decrypts incoming cookies |
| 8 | `StartSession` | Web group | Loads the database session |
| 9 | `ShareErrorsFromSession` | Web group | Flashes validation errors to views |
| 10 | `VerifyCsrfToken` | Web group | Validates `_token` field |
| 11 | `SubstituteBindings` | Web group | Route model binding resolution |
| 12 | `HandleInertiaRequests` | Web group | Injects shared props into Inertia response |
| 13 | `CheckSuspended` | Web group | **Force logs out suspended users** |
| 14 | `guest:web` | Route | Redirects already-authenticated users away from login |
| 15 | `throttle:{limiter}` | Route | Rate-limits login attempts |
| 16 | `recaptcha` | Route | Validates Google reCAPTCHA v3 token |

---

### 2. `recaptcha` Middleware Detail (`App\Http\Middleware\Recaptcha`)

```php
// Queries: settings table (cached via SettingCache)
$recaptchaKeys = SettingService::getRecaptchaKeys();
// → SELECT * FROM settings WHERE `group` = 'key.google_recaptcha'

$response = (new GoogleRecaptcha($secretKey))
    ->verify($request->input('g-recaptcha-response'), $request->ip());
```

- **Reads** `recaptcha_secret_key` from `settings` table (cached in memory)
- **Calls** Google reCAPTCHA API (`https://www.google.com/recaptcha/api/siteverify`) — **external HTTP call**
- **Score check**: if score < `recaptcha_score` setting → redirect back with error
- **Key decision**: if reCAPTCHA keys are NOT configured in settings, middleware is **skipped entirely** (passes through)

> ⚠️ **Admin login reCAPTCHA is different** (`RecaptchaAdminLoginPage`): it has a bypass condition — if the error codes include `E_MISSING_INPUT_RESPONSE` or `invalid-input-secret`, the request is allowed through. This means missing or invalid reCAPTCHA config silently passes admin login. The frontend `Recaptcha` middleware has no such bypass.

---

### 3. Controller: `AuthenticatedSessionController@store` (Fortify)

This is not a custom controller — it comes from `Laravel\Fortify`. It runs the request through an **authentication pipeline** defined in `app/Actions/AuthenticationPipeline.php`:

```php
// AuthenticationPipeline.php
return array_filter([
    config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
    RedirectIfTwoFactorAuthenticatable::class,  // Custom override
    AttemptToAuthenticate::class,               // Fortify built-in
    PrepareAuthenticatedSession::class,          // Custom override
]);
```

**Pipeline Stage 1: `EnsureLoginIsNotThrottled`** (only if no `throttle` middleware on route)
- Checks Laravel's rate limiter — skipped here since `throttle:` is already applied at route level.

**Pipeline Stage 2: `RedirectIfTwoFactorAuthenticatable`** (`App\Actions\Fortify\RedirectIfTwoFactorAuthenticatable`)
- Checks if the matched user has 2FA enabled
- **Key decision**: if `two_factor_secret` is set on the user, interrupts the pipeline and redirects to:
  - `/two-factor-challenge` (frontend login)
  - `/admin/two-factor-challenge` (admin login)
- Stores `login.id`, `login.remember`, `login.recovery.id` in session for the 2FA step

**Pipeline Stage 3: `AttemptToAuthenticate`** (Fortify built-in using custom `AuthenticateLoginAttempt`)

The custom `AuthenticateLoginAttempt` action is bound in Fortify's service provider:

```php
// App\Actions\AuthenticateLoginAttempt
$user = User::where('email', $request->email)->first();
// → SELECT * FROM users WHERE email = ?

// KEY DECISION: Admin portal gate
if ($user && LoginService::isAdminLoginAttemptRoute($request->route()) && !$user->can('system.dashboard')) {
    $user = null;  // Reject non-admin users on admin login route
}

if ($user && Hash::check($request->password, $user->password)) {
    return $user;  // Authenticate
}
// If null returned → Fortify fires ValidationException('credentials do not match')
```

**Database reads at this stage:**
```sql
SELECT * FROM users WHERE email = ? LIMIT 1
-- If user found, Spatie loads permissions:
SELECT roles.* FROM roles INNER JOIN model_has_roles ON ... WHERE model_has_roles.model_id = ?
SELECT permissions.* FROM permissions INNER JOIN role_has_permissions ON ... WHERE role_has_permissions.role_id IN (?)
```

> 🔑 **Key logic**: A frontend user (performer) trying to log in via `/admin/login` will receive "credentials do not match" — not a permission error — making enumeration harder.

**Pipeline Stage 4: `PrepareAuthenticatedSession`** (`App\Actions\Fortify\PrepareAuthenticatedSession`)

```php
// Identifies which portal this login came from
if ($request->routeIs('admin.*')) {
    LoginService::setAdminHomeUrl();  // session(['home_url' => '/admin/dashboard'])
} else {
    LoginService::setUserHomeUrl();   // session(['home_url' => '/dashboard'])
}
// Then calls parent::handle() → Fortify regenerates session, logs in the user
```

**What happens inside Fortify's `PrepareAuthenticatedSession`:**
1. Session is regenerated (`$request->session()->regenerate()`) — prevents session fixation
2. `Auth::login($user, $remember)` — sets the authenticated user
3. Fires `Illuminate\Auth\Events\Login` event (standard Laravel, no custom listeners attached here)

---

### 4. Inertia Response via `HandleInertiaRequests`

After the pipeline completes, Fortify returns a redirect to `home_url`. On the **next request** (the redirect target), `HandleInertiaRequests` runs and shares data:

```php
share(Request $request) {
    // Database queries on every authenticated Inertia page load:
    $settingService->getLogoOrDefaultUrl()        // → settings table + media table
    MenuService::getHeaderMenus($request)          // → menus + menu_items tables
    TranslationService::getLocaleOptions()          // → languages table
    SettingService::getFrontendCssUrl()             // → settings table
    ModuleService::getEnabledNames()                // → modules table
    // For non-admin users also:
    MenuService::getFrontendUserFooterMenus()       // → menus table
    MenuService::getSocialMediaMenus()              // → menus table
}
```

Then on admin routes, `SetClientAuthToken` middleware (runs post-response):
```php
// Generates a new 64-char random token
// Sets TWO non-HttpOnly cookies: buskincity_auth_client, buskincity_auth_client_expiry
// Token is also injected into Inertia::share() for JS initialization
// Validity: 2 hours; checks existing cookie before regenerating
```

---

### 5. Side Effects of Successful Login

| Side Effect | Class | Trigger | Async? |
|-------------|-------|---------|--------|
| Session regeneration | Fortify | Pipeline completion | Sync |
| `home_url` written to session | `PrepareAuthenticatedSession` | Pipeline completion | Sync |
| `buskincity_auth_client` cookie set | `SetClientAuthToken` | Post-response | Sync |
| Inertia shared data hydration | `HandleInertiaRequests` | Next request | Sync |

---

## Flow 2 — Two-Factor Authentication (`POST /two-factor-challenge`)

### Route Entry

```
routes/web.php
  Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
    ->middleware([
        'guest:web',
        'throttle:{twoFactorLimiter}',
        'recaptcha',
    ]);
```

### Execution Path

1. `recaptcha` middleware validates token (same as login)
2. `TwoFactorAuthenticatedSessionController@store` (custom, extends Fortify)
   - Calls `parent::store()` → Fortify validates the TOTP code against `two_factor_secret` (decrypted from DB)
   - **DB read**: `SELECT two_factor_secret FROM users WHERE id = ?` (from session `login.id`)
   - On success: calls `Auth::login()`, regenerates session
3. **Custom override**: after parent completes:
   ```php
   if (auth()->check()) {
       LoginService::setHomeUrl($request);  // Restores correct home_url in session
   }
   ```

> ⚠️ **Hidden dependency**: `LoginService::setHomeUrl()` uses `$request->routeIs('admin.*')` to decide which `home_url` to set. This means the route name format (`admin.*`) is a structural hard dependency — renaming admin routes would break the 2FA redirect logic.

---

## Flow 3 — User Registration (`POST /register`)

### Route Entry

```
routes/web.php
  Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware([
        'guest:web',
        'recaptchaRegisterPage',
    ]);
```

### Execution Path

**Step 1:** `RecaptchaRegisterPage` middleware validates reCAPTCHA (inherits `Recaptcha`, same logic).

**Step 2:** `RegisteredUserController@store` (custom, extends Fortify):

```php
// 1. Calls parent::store() which invokes CreateNewUser action
$response = parent::store($request, $creator);

// After parent:
// 2. Sets home_url in session
if (auth()->check()) {
    LoginService::setHomeUrl($request);
}

// 3. Notifies Super Administrator of new registration
$superAdmin = User::role('Super Administrator')->first();
// → SELECT users.* FROM users INNER JOIN model_has_roles ... WHERE roles.name = 'Super Administrator'
if ($superAdmin) {
    Notification::send($superAdmin, new NewUserRegisteredNotification(auth()->user()));
}
```

**Step 3:** Inside Fortify's `parent::store()`, the `CreateNewUser` action runs:

```php
// Validation
Validator::make($input, [
    'first_name' => ['required', 'string', 'max:128'],
    'last_name'  => ['required', 'string', 'max:128'],
    'email'      => ['required', 'email', 'unique:users'],
    'password'   => $this->passwordRules(),
]);

// Get default language
$input['language_id'] = LanguageService::getDefaultId();
// → SELECT id FROM languages WHERE is_default = 1 LIMIT 1

// Create user (unverified state)
$user = User::factory()->unverified()->create([...]);
// → INSERT INTO users (first_name, last_name, email, password, language_id, unique_key, email_verified_at=null) VALUES (?)

// Save default meta (country from IP)
$user->saveDefaultMetas();
// → IPService::getCountryCode() → calls IPRegistry API (external HTTP)
//   or falls back to torann/geoip
// → INSERT INTO user_metas (user_id, key, value) VALUES (?, 'country', ?)
```

**Step 4: Automatic events fired by Fortify/Laravel after user creation:**

```php
// EventServiceProvider:
Registered::class => [SendEmailVerificationNotification::class]
```

`SendEmailVerificationNotification` listener calls `$user->sendEmailVerificationNotification()`:

```php
// User model
public function sendEmailVerificationNotification()
{
    $verifyEmail = (new VerifyEmail())->locale($this->languageCode);

    if ($this->can('system.dashboard')) {
        $verifyEmail = $verifyEmail->admin();  // uses admin verification URL
    }

    $this->notify($verifyEmail);
    // → queues VerifyEmail notification via driver (mail)
}
```

**Database writes during registration:**

```sql
INSERT INTO users (first_name, last_name, email, password, language_id, unique_key, email_verified_at, ...) VALUES (...)
INSERT INTO user_metas (user_id, key, value) VALUES (?, 'country', ?)
-- If notification persisted (database channel): INSERT INTO notifications (...)
```

**Side effects:**

| Side Effect | Class | Channel | Async? |
|-------------|-------|---------|--------|
| Email verification link sent to registrant | `VerifyEmail` notification | Mail | Queued |
| New registration notification to Super Admin | `NewUserRegisteredNotification` | Mail | Queued (uses `Queueable` trait) |
| Country meta saved from IP lookup | `User::saveDefaultMetas()` | DB | Sync + External HTTP |
| `home_url` set in session | `LoginService::setHomeUrl()` | Session | Sync |

---

## Flow 4 — Email Verification (`GET /email/verify/{id}/{hash}`)

### Route Entry

```
routes/web.php
  Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth:sanctum', 'signed', 'throttle:6,1']);
```

### Execution Path

1. `auth:sanctum` — user must be logged in
2. `signed` — validates URL signature (`hash` = `sha1($user->email)` + expiry) — **forged or expired links abort with 403**
3. `throttle:6,1` — max 6 verification attempts per minute

**Controller logic (`VerifyEmailController`):**

```php
if ($request->user()->hasVerifiedEmail()) {
    return app(VerifyEmailResponse::class);  // Already verified → redirect to home
}

if ($request->user()->markEmailAsVerified()) {
    // → UPDATE users SET email_verified_at = NOW() WHERE id = ?
    event(new Verified($request->user()));
    // Standard Laravel event — no custom listeners registered
}

return app(VerifyEmailResponse::class);
// → Custom response class redirects to home_url
```

> 🔑 **Key decision**: `VerifyEmailResponse` is a custom Fortify response class bound in the service provider. It routes differently for admin vs frontend users based on `home_url` in session.

---

## Flow 5 — Forgot Password (`POST /forgot-password`)

### Route Entry

```
routes/web.php
  Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware([
        'guest:web',
        'recaptchaForgotPasswordPage',
        'throttle:defaultRequest',
    ]);
```

### Execution Path

**Step 1:** `RecaptchaForgotPasswordPage` validates reCAPTCHA (inherits `Recaptcha`).

**Step 2:** `PasswordResetLinkController@store` (custom, extends Fortify):

```php
$rules = [
    'email' => [new SuspendedUserEmail()],  // Custom rule
];

// Extra check on admin reset route:
if ($request->routeIs('admin.*')) {
    // Validates that the email belongs to a backend (admin) user
    $rules['email'][] = function ($attributes, $value, $fail) {
        $user = User::email($value)->first();
        // → SELECT id, email FROM users WHERE email = ?
        
        // Must have system.dashboard permission OR be Super Administrator
        $isBackendUser = $user?->roles->contains(fn($r) => $r->hasPermissionTo('system.dashboard'))
                      || $user?->isSuperAdministrator;
        
        if (!$isBackendUser) {
            $fail('...');
        }
    };
}
```

**`SuspendedUserEmail` rule check:**
```php
// → SELECT is_suspended FROM users WHERE email = ?
// If is_suspended = true → validation fails with suspension error
```

**Step 3:** `parent::store()` (Fortify) sends the password reset notification:
```php
// Fortify calls: Password::broker()->sendResetLink(['email' => $email])
// This:
// 1. Generates a token: $token = Str::random(64) (hashed before storage)
// 2. INSERT INTO password_resets (email, token, created_at) VALUES (?)
// 3. Calls $user->sendPasswordResetNotification($token)
```

**`User::sendPasswordResetNotification()` custom override:**
```php
public function sendPasswordResetNotification($token)
{
    app()->setLocale($this->languageCode);
    // → Sets locale from user's language (reads language_id from users + languages table)
    
    $this->notify(new ResetPassword($token));
    // → Queues reset password email in user's locale
}
```

**Side effects:**

| Side Effect | Class | Trigger | Async? |
|-------------|-------|---------|--------|
| Reset token inserted | Laravel Password Broker | `sendResetLink()` | Sync |
| Password reset email sent | `ResetPassword` notification | Mail queue | Queued |
| Locale set for email | `User::sendPasswordResetNotification()` | Per-user language | Sync |

**DB writes:**
```sql
INSERT INTO password_resets (email, token, created_at) VALUES (?, bcrypt(?), NOW())
-- or UPDATE if row already exists (depends on broker implementation)
```

---

## Flow 6 — Password Reset (`POST /reset-password`)

### Route Entry

```
routes/web.php
  Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware(['guest:web']);
```

### Execution Path

**Step 1:** `NewPasswordController@store` (custom, extends Fortify):
```php
// Extra validation: check if email belongs to suspended user
$request->validate([
    'email' => new SuspendedUserEmail(),
]);
// → SELECT is_suspended FROM users WHERE email = ?
// If suspended → abort with error message

return parent::store($request);
```

**Step 2:** Fortify validates token:
```php
// SELECT * FROM password_resets WHERE email = ?
// Hash::check($request->token, $record->token)
// Check if created_at + expiry window > now()
```

**Step 3:** On valid token, calls `ResetUserPassword` action:
```php
// App\Actions\Fortify\ResetUserPassword
$user->password = Hash::make($input['password']);
$user->save();
// → UPDATE users SET password = ?, remember_token = NULL WHERE id = ?
```

**Step 4:** Cleanup:
```php
// Fortify deletes the used token
DELETE FROM password_resets WHERE email = ?

// Session is regenerated, user is logged in
Auth::login($user);
$request->session()->regenerate();
```

---

## Flow 7 — OAuth Social Login (`GET /oauth/{provider}/callback`)

### Route Entry

```
routes/web.php
  Route::get('/oauth/{provider}/callback', [CustomOAuthController::class, 'handleProviderCallback'])
    ->middleware(config('socialstream.middleware', ['web']));
```

### Execution Path

**Step 1:** `CustomOAuthController` extends Socialstream's `OAuthController`. The parent `handleProviderCallback` is called:

```php
// Socialstream OAuthController flow:
$providerUser = Socialite::driver($provider)->user();
// → External OAuth API call to Google/Facebook/Twitter, exchanges code for user data
```

**Step 2:** Branch: Existing user with this provider account?

```php
// SELECT * FROM connected_accounts WHERE provider = ? AND provider_id = ?
$account = ConnectedAccount::where('provider', $provider)
                            ->where('provider_id', $providerUser->getId())
                            ->first();
```

**Branch A: New user (no account, no matching email)**
```php
// CreateUserFromProvider::create()
DB::transaction(function () use ($provider, $providerUser) {
    $name = UserService::splitName($providerUser->getName());

    $user = User::factory()->create([
        'first_name'  => $name['firstName'],
        'last_name'   => $name['lastName'],
        'email'       => $providerUser->getEmail(),
        'language_id' => Language::where('code', 'en')->value('id'),
        'password'    => null,  // No password — OAuth only
    ]);
    // → INSERT INTO users (...)
    
    $user->markEmailAsVerified();
    // → UPDATE users SET email_verified_at = NOW() WHERE id = ?
    // Note: no verification email needed — provider already verified it

    // Profile photo from OAuth provider avatar URL
    $user->setProfilePhotoFromUrl($providerUser->getAvatar());
    // → Downloads avatar, uploads to Cloudinary
    // → INSERT INTO media (...)
    // → UPDATE users SET profile_photo_media_id = ? WHERE id = ?
    
    // Create connected account record
    CreateConnectedAccount::create($user, $provider, $providerUser);
    // → INSERT INTO connected_accounts (user_id, provider, provider_id, token, ...)

    // Country from IP
    $user->setMeta('country', IPService::getCountryCode());
    $user->saveMetas();
    // → INSERT INTO user_metas (user_id, key, value)
});
```

**Branch B: Existing user, same provider account → login**
```php
// Update tokens
UpdateConnectedAccount::handle($user, $account, $provider, $providerUser);
// → UPDATE connected_accounts SET token = ?, refresh_token = ?, expires_at = ? WHERE id = ?

// Login
$this->login($user);  // Custom override in CustomOAuthController
// → LoginService::setUserHomeUrl() → session(['home_url' => '/dashboard'])
// → Auth::login($user)
```

**Branch C: Authenticated user links a new OAuth provider**
```php
// CustomOAuthController::alreadyAuthenticated()
if (!$account) {
    CreateConnectedAccount::create($user, $provider, $providerUser);
    // → INSERT INTO connected_accounts (...)
    redirect()->route('user.profile.show')->with('message', '...');
}
```

**Branch D: Provider account already linked to a DIFFERENT user**
```php
// CustomOAuthController::alreadyAuthenticated()
return redirect()->route(...)->withErrors('This account is already associated...');
```

**Side effects of New User OAuth registration:**

| Side Effect | Database | Async? |
|-------------|---------|--------|
| User created (no email verification sent) | `users` | Sync |
| Email pre-verified | `users.email_verified_at` | Sync |
| OAuth tokens stored | `connected_accounts` | Sync |
| Avatar downloaded and uploaded to Cloudinary | `media`, `users.profile_photo_media_id` | Sync |
| Country meta from IP | `user_metas` | Sync + External HTTP |

> ⚠️ **No `Registered` event is fired** for OAuth-created users → `SendEmailVerificationNotification` listener is NOT triggered → no verification email. This is intentional (provider already verified the email) but means the `NewUserRegisteredNotification` to Super Admin is also **not sent** for OAuth registrations.

---

## Flow 8 — Guest Access Guard Behavior

```
routes/web.php
  middleware('guest:{guard}')
  → App\Http\Middleware\RedirectIfAuthenticated
```

```php
// Custom override with dual-portal awareness
if (Auth::user()->can('system.dashboard') && $request->routeIs('admin.login') && LoginService::isAdminHomeUrl()) {
    return redirect(config('fortify.admin_home'));
}
if ($request->routeIs('login') && LoginService::isUserHomeUrl()) {
    return redirect(config('fortify.home'));
}
```

> 🔑 **Key decision**: An admin user who is logged in and visits `/login` is NOT redirected to admin home unless `LoginService::isUserHomeUrl()` is true. This means a subtle corner case: if an admin's session has `home_url = null`, they're allowed onto the login page even while authenticated.

---

## Flow 9 — Protected Route Access (Every Authenticated Request)

Routes protected by `['auth:sanctum', 'verified', 'ensureLoginFromLoginRoute']` execute this chain on every request:

```
1. auth:sanctum
   → Resolves user from session (stateful) or Bearer token (API)
   → On failure: redirects to login page (or returns 401 for JSON)

2. verified (UserEmailIsVerified)
   → Checks $user->hasVerifiedEmail() (email_verified_at IS NOT NULL)
   → On failure: redirects to verification.notice (or admin.verification.notice)
   → DB: no query — email_verified_at is loaded with the session user

3. ensureLoginFromLoginRoute (EnsureLoginFromLoginRoute)
   → Checks session home_url === config('fortify.home')
   → JSON/Inertia requests: if no home_url, sets it and passes through
   → Non-JSON requests with wrong portal: redirect to home_url
   → Critical guard against portal mixing

4. CheckSuspended (global web middleware)
   → auth()->user()->is_suspended check (no extra query — in-memory)
   → On suspended: force logout + session invalidate + redirect to login
```

**Admin routes add one more:**

```
5. setClientAuthToken (post-controller middleware)
   → Reads buskincity_auth_client cookie
   → If missing or expired: generates new 64-char random token
   → Sets two non-HttpOnly cookies + injects into Inertia shared data
```

---

## Flow 10 — Scheduled: Remove Unverified Users (Daily)

```
Console\Kernel → schedule->job(new RemoveNotVerifiedUser())->daily()
```

```php
// RemoveNotVerifiedUser job
User::whereNull('email_verified_at')
    ->where('created_at', '<=', Carbon::now()->subMonths(1)->format('Y-m-d') . ' 00:00:00')
    ->whereDoesntHave('connectedAccounts')  // OAuth-created users are exempt
    ->forceDelete();
// → Hard-delete users: no soft-delete, no cascade guard
```

**Critical behaviors:**
- OAuth-registered users are **exempt** even if unverified (by `whereDoesntHave('connectedAccounts')`)
- `forceDelete()` bypasses soft-delete — records are permanently removed
- No email is sent to the user before deletion

> ⚠️ **Side effect**: `forceDelete()` on a User may leave orphaned records in `user_metas`, `media`, `connected_accounts`, `model_has_roles` if cascades are not set up at the DB level. This is a migration risk item.

---

## Flow 11 — Admin-Triggered Password Reset (`POST /admin/users/password-reset/send`)

```php
// SendUserPasswordResetEmailController
// Dispatches: App\Jobs\SendResetPasswordLink

SendResetPasswordLink::dispatch($emails);
// Queued job:
foreach ($emails as $email) {
    $token = Str::random(64);
    DB::table('password_resets')->insert([
        'email'      => $email,
        'token'      => bcrypt($token),   // Note: bcrypt, not Hash::make — consistent but hardcoded
        'created_at' => Carbon::now(),
    ]);
    $url = route('password.reset', ['token' => $token, 'email' => $email]);
    Mail::to($email)->send(new ResetPasswordPerformer($url));
    // → Sends ResetPasswordPerformer mailable synchronously (Mail::send, not ::queue)
}
```

> ⚠️ **Inconsistency**: This job uses `Mail::send()` (synchronous) even though it's dispatched as a queue job. If the queue worker is slow, email sending blocks the worker thread. Compare to `User::sendPasswordResetNotification()` which uses `$this->notify()` (async by default if notification uses `Queueable`).

---

## Session Architecture Summary

```
session driver: database
table: sessions
key fields: id, user_id, ip_address, user_agent, payload, last_activity

Critical session keys:
  home_url           → 'fortify.home' or 'fortify.admin_home' — portal context
  login.id           → user ID during 2FA pending state
  login.remember     → "remember me" flag during 2FA pending state
  login.recovery.id  → same as login.id (redundant, from Fortify)
```

---

## Hidden Dependencies Map

```
LoginService::setHomeUrl()
  ← Called by: PrepareAuthenticatedSession, RegisteredUserController,
               TwoFactorAuthenticatedSessionController, CustomOAuthController::login()
  → Writes 'home_url' to session
  → Read by: EnsureLoginFromLoginRoute, EnsureLoginFromAdminLoginRoute,
             RedirectIfAuthenticated

SettingService (cached via SettingCache)
  ← Called by: Recaptcha, RecaptchaAdminLoginPage, LoginService, HandleInertiaRequests
  → Reads from: settings table (cached in memory per request)

IPService / IPRegistryService
  ← Called by: CreateNewUser (via saveDefaultMetas()), CreateUserFromProvider
  → Calls: https://api.ipregistry.co/ (external HTTP — can fail silently)
  → Writes country to: user_metas table

Inertia HandleInertiaRequests
  ← Runs on every web request after auth
  → Queries: settings, media, menus, menu_items, languages, modules tables

SuspendedUserEmail rule
  ← Used by: NewPasswordController, PasswordResetLinkController
  → Queries: users table (is_suspended field)
```

---

## Observers Affecting Auth Models

Registered in `EventServiceProvider::boot()`:

| Observer | Model | Hooks Fired | Relevant to Auth |
|----------|-------|-------------|-----------------|
| `SettingObserver` | `Setting` | after save | Clears `SettingCache` — affects reCAPTCHA key reads |
| `RoleObserver` | `Role` | after create/update/delete | Clears permission cache |
| `MediaObserver` | `Media` | after delete | Deletes from Cloudinary (profile photo cleanup) |

> `User` model has **no observer** registered — model events (`creating`, `created`, `deleting`) are not intercepted by any Observer class.

---

## Error & Edge Case Behavior

| Scenario | What Happens |
|----------|-------------|
| Wrong password | `AuthenticateLoginAttempt` returns `null` → Fortify throws `ValidationException` → redirected back with `'email' => __('auth.failed')` |
| Suspended user logs in | `CheckSuspended` (global middleware) force-logs them out on the next request and redirects to login |
| Suspended user attempts password reset | `SuspendedUserEmail` validation rule blocks it at the `forgot-password` step |
| Admin user tries frontend login at `/login` | `AuthenticateLoginAttempt` returns the user (no gate check on non-admin route) — they can log in as frontend user |
| Frontend user tries admin login at `/admin/login` | `AuthenticateLoginAttempt` nullifies the user → "credentials don't match" |
| Expired verification link | `signed` middleware returns 403 abort |
| reCAPTCHA keys not configured in settings | reCAPTCHA middleware skips validation entirely (passes through) |
| IPRegistry API failure during registration | `saveDefaultMetas()` fails silently — user is still created; country stays null |
| 2FA code correct but session missing `login.id` | Fortify returns unauthenticated — no 2FA completion |
| OAuth provider returns no email | `CreateUserFromProvider` would fail at `INSERT INTO users` — `email` is required |

---

*End of Authentication & Session Management Execution Flow*
