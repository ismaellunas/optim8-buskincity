# User Creation, Approval, Roles & Access Control — Technical Map

> Generated: 2026-06-01
> Purpose: Pre-refactor technical map for the user approval flow and adding a new role.
> Scope: Account creation → approval → role assignment → permission/access control, plus all connected features.
> Status: **Inspection-only. No code was modified.**

---

## 1. Executive Summary

This is a **Laravel 9 + Fortify + Jetstream + Inertia/Vue 3** application ("BuskinCity") with a **dual-portal architecture** (frontend "performer" portal and an `/admin` panel sharing one `users` table). Authorization is built on **`spatie/laravel-permission`** (roles + permissions + wildcard permissions), and the front end is rendered through Inertia pages under `resources/js/Pages/`.

Key findings relevant to the planned refactor:

- **There is no explicit "approval" column or state machine.** A user is effectively "active/approved" when three implicit conditions are met: `email_verified_at IS NOT NULL`, `is_suspended = false`, and **a role is assigned** (a user with no role has no permissions and no public page). The `User::scopeAvailable()` / `getIsAvailableAttribute()` encode "active" as *email verified + not suspended* only — role is a separate, unenforced dimension.
- **There are two completely different ways a user becomes an active member:**
  1. **Self-registration** (`POST /register`) → creates an *unverified, role-less* user, notifies Super Admin. No role is assigned automatically.
  2. **Form-Builder "Automate User Creation"** (the real "application → approval" flow) → an admin reviews a submitted application (a `FormEntry`) and clicks **Create/Update User**, which creates the user **and assigns the role configured on the form**. This is the de-facto approval gate.
  3. Admins can also create users directly (`POST /admin/users`) and assign roles in one step.
- **Roles are seeded in two disconnected places.** Core roles (`Super Administrator`, `Administrator`, `Author`, `Performer`) come from `RoleSeeder` + `UserAndPermissionSeeder` (both in `DatabaseSeeder`). The newest role, **`city_administrator`**, is created by `CityAdministratorSeeder` which is **NOT registered in `DatabaseSeeder`** — a notable inconsistency and the best existing template for adding a new role.
- **Role/permission values are heavily hardcoded** as bare strings across config, seeders, models, policies, middleware, and route definitions. `config/permission.php` centralizes a few canonical names but most checks bypass it and use literal strings (e.g. `'Super Administrator'`, `'city_administrator'`, `'system.dashboard'`).
- **Two naming conventions coexist for roles**: human-readable Title Case (`Super Administrator`) vs. snake_case (`city_administrator`). This will bite a new role if not chosen deliberately.
- **Site/branch assignment** exists only for City Administrators via the `city_user` pivot (`User::adminCities()`), wired separately from the role system. The **"user code"** concept is the `users.unique_key` column (16-char unique, used for public profile URLs).

The system is functional but the approval/role wiring is **implicit, duplicated, and string-driven**. Section 7 lists concrete refactor points; Section 8 is the checklist for adding a new role.

---

## 2. Current Process Flow

### 2.1 End-to-end (high level)

```
                       ┌──────────────────────────────────────────────────┐
                       │             ACCOUNT CREATION (3 paths)             │
                       └──────────────────────────────────────────────────┘
 (A) Self register          (B) Admin creates user        (C) Public application form
 POST /register             POST /admin/users             POST (FormBuilder public form)
   │                          │                              │
   ▼                          ▼                              ▼
 CreateNewUser action     UserController@store           FormEntry created (no user yet)
   - validate                - validate (UserStoreRequest)   │ admin reviews entry in
   - create UNVERIFIED       - create user                   │ /admin/form-builders/{id}/entries
   - language default        - savePassword()                ▼
   - saveDefaultMetas()      - saveDefaultMetas()         AutomateUserCreationController
   - fire Registered event   - verifiyEmail() (auto)         @createOrUpdateUser
   │                          - assignRole(role)              - validateFormEntry (email not a
   ▼                          - sync cities (if city admin)     protected admin/superadmin)
 SendEmailVerification          │                             - createOrUpdate():
 + NewUserRegistered            │                                 * create/find user
   notification to              │                                 * assignRole(mapped role)  ◄─ APPROVAL
   Super Admin                  │                                 * map form fields → user metas
   │                            │                                 * profile photo
   ▼                            │                             - mark automate_user_creation_at
 User clicks email link         │                             - (optional) send credentials email
 GET /email/verify/{id}/{hash}  │                                │
   - markEmailAsVerified()      │                                │
   │                            │                                │
   └──────────────┬─────────────┴────────────────┬───────────────┘
                  ▼                               ▼
        ┌───────────────────────────────────────────────┐
        │  ACTIVE USAGE GATE (checked on every request)   │
        │  auth:sanctum → verified (email_verified_at)    │
        │  → ensureLoginFrom{Admin}LoginRoute             │
        │  → CheckSuspended (is_suspended)                │
        │  → can:<permission> (per-route/policy via role) │
        └───────────────────────────────────────────────┘
```

### 2.2 "Approval" defined operationally

There is no `approved`/`status` enum. A user can do meaningful things only when:

| Dimension | Stored as | Set by | Enforced by |
|-----------|-----------|--------|-------------|
| Email verified | `users.email_verified_at` | email link, OAuth, admin `verifiyEmail()`, FormBuilder (not set!) | `verified` middleware, `scopeAvailable`, `scopeEmailVerified` |
| Not suspended | `users.is_suspended` (bool, default false) | `User::suspend()/unsuspend()` | `CheckSuspended` middleware, `scopeAvailable`, `getIsAvailableAttribute` |
| Has a role (→ permissions) | `model_has_roles` pivot | `assignRole()` in admin create, FormBuilder mapping | every `can:` check / policy |
| Public page visible | derived | role having `public_page.profile` + `isAvailable` | `getHasPublicPageAttribute` |

> ⚠️ **The FormBuilder "Automate User Creation" path does NOT call `verifiyEmail()`** (see `AutomateUserCreationService::createOrUpdateUser`). Approved-by-form users may have `email_verified_at = null`, blocking them at the `verified` middleware until they verify or an admin verifies them. This is a real edge case for the refactor.

### 2.3 Login & active usage (summarized from `auth-execution-flow.md`)

`POST /login` (frontend) / `POST /admin/login` (admin) run through Fortify's `AuthenticationPipeline` (`app/Actions/AuthenticationPipeline.php`). The admin gate is enforced inside `App\Actions\AuthenticateLoginAttempt`: a user without `system.dashboard` permission is rejected on the admin login route. `PrepareAuthenticatedSession` records `home_url` in the session to keep the two portals separate.

---

## 3. File-by-File Map

### 3.1 Account creation & registration

| File | Role in flow |
|------|--------------|
| `routes/web.php` | `POST /register`, `/login`, `/email/verify/...`, password reset routes |
| `app/Http/Controllers/RegisteredUserController.php` | Overrides Fortify register; sets `home_url`; notifies Super Admin via `NewUserRegisteredNotification` |
| `app/Actions/Fortify/CreateNewUser.php` | Fortify `CreatesNewUsers`; validates + creates **unverified** user, default language, `saveDefaultMetas()` |
| `app/Actions/Fortify/PasswordValidationRules.php` | Shared password rules (used by store requests too) |
| `app/Models/User.php` | Central model: roles trait, status helpers, `assignRole` (via `HasRoles`), `suspend/unsuspend`, `verifiyEmail`, `saveDefaultMetas`, `adminCities`, `isCityAdmin`, role accessors |
| `database/factories/UserFactory.php` | Generates `unique_key` (user code) via `Url::randomDigitSegment`, default verified state, `unverified()` state |
| `app/Notifications/NewUserRegisteredNotification.php` | Sent to Super Admin on self-registration |
| `app/Notifications/VerifyEmail.php` | Email verification (admin vs frontend variant) |
| `app/Services/IPService.php` | Country lookup used by `saveDefaultMetas()` |
| `app/Services/LanguageService.php` | Default language id for new users |

### 3.2 Admin-driven user management

| File | Role in flow |
|------|--------------|
| `app/Http/Controllers/UserController.php` | Admin CRUD: `store` (create + assignRole + sync cities), `update` (re-assign role), `suspend`/`unsuspend`, `destroy`, `updatePassword`, trashed records |
| `app/Http/Controllers/CrudController.php` | Base controller (flash messages, `getData`) |
| `app/Http/Requests/UserStoreRequest.php` | Validates create; `role` must be in allowed role ids (`withoutSuperAdmin` unless actor is Super Admin) |
| `app/Http/Requests/UserUpdateRequest.php` | Validates update |
| `app/Http/Requests/UserDestroyRequest.php` | Reassign-vs-delete validation |
| `app/Http/Requests/UserPasswordRequest.php` | Admin password set |
| `app/Services/UserService.php` | `getRecords` (search + `inRoles` scope, hides Super Admin from non-super), `getRoleOptions` (`Role::withoutSuperAdmin`), `reassignResources`, `deleteResources`, `hashPassword` |
| `app/Actions/Jetstream/DeleteUser.php` | Cascade cleanup on delete (photo, tokens, connected accounts) |
| `app/Http/Controllers/SendUserPasswordResetEmailController.php` | Admin-triggered password reset emails |

### 3.3 The application → approval (Form-Builder) flow

| File | Role in flow |
|------|--------------|
| `modules/FormBuilder/Http/Controllers/FormEntryController.php` | Lists/show form entries; exposes `can.automate_user_creation` per entry |
| `modules/FormBuilder/Http/Controllers/AutomateUserCreationController.php` | `save` (configure mapping rules + role), `createOrUpdateUser` (**the approval action**), `confirmation` (preview) |
| `modules/FormBuilder/Services/AutomateUserCreationService.php` | `createOrUpdate` → `createOrUpdateUser`, `assignRole`, `syncUserMetas`, `updateProfilePhoto`, `markAutomateActionIsDone`, `getRoleOptions` (`Role::withoutAdmin`) |
| `modules/FormBuilder/Http/Requests/AutomateUserCreationRequest.php` | Validates mapping config incl. `role` |
| `modules/FormBuilder/Policies/FormEntryPolicy.php` | `automateUserCreation` gate (`form_builder.automate_user_creation` + entry not yet processed + mandatory fields mapped) |
| `modules/FormBuilder/Entities/FormEntry.php` | The "application" record; `automate_user_creation_at` meta marks "approved/processed" |
| `modules/FormBuilder/Entities/FormMappingRule.php` | Stores field→user-column and the `role` mapping (`group = 'role'`, `to.role = <roleId>`) |
| `modules/FormBuilder/Emails/AutomateUserCreationEmail.php` / `AutomateUserUpdateEmail.php` | Credentials/notification emails |
| `modules/FormBuilder/Routes/web.php` | Routes for entries + automate-user-creation |

### 3.4 Roles, permissions & enforcement

| File | Role in flow |
|------|--------------|
| `config/permission.php` | Spatie config + canonical names: `super_admin_role`, `admin_or_super_admin`, `role_names.{admin,performer,super_admin}`; `enable_wildcard_permission = true` |
| `app/Models/Role.php` | Extends Spatie Role; scopes `withoutSuperAdmin`, `withoutAdmin`; `isAdminRole` accessor |
| `app/Models/Permission.php` | Extends Spatie Permission |
| `app/Http/Controllers/RoleController.php` | Role CRUD (Super-Admin only via `RolePolicy`); `syncPermissions` |
| `app/Http/Requests/RoleRequest.php` | Role validation |
| `app/Services/RoleService.php` | `getPermissionOptions` (grouped by enabled module), `getRecords` |
| `app/Policies/RolePolicy.php` | All actions require Super Admin; `delete`/`editName` blocked for the Administrator role |
| `app/Policies/UserPolicy.php` | User CRUD authorization, suspend/unsuspend, password, Stripe, trashed |
| `app/Policies/BasePermissionPolicy.php` | Generic `browse/read/edit/add/delete` → `can('<base>.<verb>')` |
| `app/Providers/AuthServiceProvider.php` | Policy bindings + **`Gate::after` granting Super Administrator everything** |
| `database/seeders/RoleSeeder.php` | Seeds 4 core roles |
| `database/seeders/PermissionSeeder.php` | Seeds the full permission list (wildcards + granular) |
| `database/seeders/UserAndPermissionSeeder.php` | Assigns permissions to roles + creates Super Admin & Admin users |
| `database/seeders/CityAdministratorSeeder.php` | Creates `city_administrator` role + city permissions (**not in DatabaseSeeder**) |
| `database/seeders/PerformerSeeder.php` | Seeds performer demo users |
| `database/seeders/DatabaseSeeder.php` | Master seed order (does **not** include CityAdministratorSeeder) |

### 3.5 Middleware (access control on every request)

| File | Purpose |
|------|---------|
| `app/Http/Middleware/CheckSuspended.php` | Force-logout suspended users (global web) |
| `app/Http/Middleware/UserEmailIsVerified.php` (`verified`) | Blocks unverified users |
| `app/Http/Middleware/EnsureLoginFromLoginRoute.php` / `EnsureLoginFromAdminLoginRoute.php` | Keep frontend/admin portals separate |
| `app/Http/Middleware/RedirectIfAuthenticated.php` (`guest`) | Portal-aware guest redirect |
| `app/Actions/AuthenticateLoginAttempt.php` | Admin login gate (`system.dashboard`) |
| Laravel `can:` middleware | Per-route permission checks (e.g. `can:system.dashboard`, `can:system.theme`) |
| `app/Http/Middleware/HandleInertiaRequests.php` | Shares auth/user/permission data to Vue |

### 3.6 Site/branch assignment (City Administrator)

| File | Purpose |
|------|---------|
| `database/migrations/2025_12_03_021740_create_cities_table.php` | `cities` table |
| `database/migrations/2025_12_03_021806_create_city_user_table.php` | `city_user` pivot (user↔city, cascade delete, unique pair) |
| `database/migrations/2025_12_03_021810_add_city_id_to_spaces_table.php` | City scoping for spaces |
| `database/migrations/2025_12_03_021814_add_city_id_to_space_events_table.php` | City scoping for events |
| `app/Http/Controllers/Api/CityController.php` | City options |
| `app/Http/Controllers/Api/CityUserController.php` | Get/set a user's cities (`->can('update','user')`) |
| `app/Models/User.php` → `adminCities()`, `isCityAdmin()`, `isCityAdministrator()` | City-admin relationship + checks |
| `resources/js/Biz/Form/CitySelect.vue` | City picker UI |
| `scripts/test_city_admin.php`, `tests/Feature/CityAdministratorTest.php` | City-admin tests/scripts |

### 3.7 Frontend (Inertia/Vue) screens

| Page component | Screen |
|----------------|--------|
| `resources/js/Pages/User/Index.vue` (+ `List`, `ListItem`, `ListDeleted`) | Admin user listing, role filter, suspend/delete |
| `resources/js/Pages/User/Create.vue` | Create user (role select, cities for city admin, photo, language) |
| `resources/js/Pages/User/Edit.vue` (+ `FormProfile`, `FormPassword`) | Edit user, change role, manage cities |
| `resources/js/Pages/User/ModalFormDelete.vue` / `ModalFormResetPassword.vue` | Delete (reassign) / reset password modals |
| `resources/js/Pages/Role/Index.vue` / `Create.vue` / `Edit.vue` / `Form.vue` | Role + permission management (Super Admin) |
| `resources/js/Pages/FormBuilder/Entries.vue` / `EntryDetail.vue` | Application entries + "Create/Update user" (approval) button |
| Auth pages (Fortify/Jetstream published views) | Register / login / verify / reset |

---

## 4. Database / Model Map

### 4.1 `users` (migration `2014_10_12_000000_create_users_table.php`)

| Column | Type | Notes / relevance |
|--------|------|-------------------|
| `id` | bigint PK | |
| `unique_key` | string(16) unique | **"user code"** — used for public profile URLs (`/profile/{user:unique_key}`) |
| `first_name`, `last_name` | string(128) | |
| `email` | string | Partial-unique index where `deleted_at IS NULL` (Postgres) |
| `email_verified_at` | timestamp nullable | **approval/active dimension #1** |
| `password` | string | nullable in practice for OAuth users |
| `remember_token` | | |
| `current_team_id` | bigint nullable | Jetstream teams (unused feature) |
| `profile_photo_path` | string(2048) nullable | legacy; actual photo via `profile_photo_media_id` (added by later migration) |
| `is_suspended` | boolean default false | **approval/active dimension #2** |
| `deleted_at` | softDeletes | |
| `timestamps` | | `created_at` exposed as `registered_at` accessor |

> Note: `profile_photo_media_id` and `language_id` are added by separate migrations and are in `$fillable`.

### 4.2 Spatie RBAC tables

| Table | Purpose |
|-------|---------|
| `roles` | `id`, `name`, `guard_name` (always `web`) |
| `permissions` | `id`, `name`, `guard_name` |
| `model_has_roles` | user↔role (morph: `model_id`, `model_type`) — **where role assignment is stored** |
| `model_has_permissions` | direct user↔permission (rarely used here) |
| `role_has_permissions` | role↔permission |

### 4.3 Supporting tables

| Table | Purpose |
|-------|---------|
| `user_metas` | arbitrary per-user meta (country, mapped application form fields) via `HasMetas` |
| `cities` | branch/site entities |
| `city_user` | **branch/site assignment** for City Administrators (user↔city) |
| `connected_accounts` | OAuth (Socialstream) tokens |
| `media` | profile photos (Cloudinary) |
| `password_resets` | reset tokens |
| `sessions` | DB session store (holds `home_url`) |
| `form_entries` / `form_mapping_rules` (FormBuilder module) | applications + role/field mapping config |

### 4.4 Key models & relationships

```
User (HasRoles, HasMetas, HasConnectedAccounts, SoftDeletes, TwoFactorAuthenticatable, LunarUser)
 ├─ roles()            → model_has_roles → Role        (Spatie)
 ├─ metas()            → user_metas
 ├─ adminCities()      → city_user → City              (branch assignment)
 ├─ profilePhoto()     → media
 ├─ connectedAccounts()→ connected_accounts
 └─ originLanguage()   → languages

Role (extends Spatie Role) ── role_has_permissions ── Permission
```

---

## 5. Role & Permission Map

### 5.1 Current roles

| Role name | Defined in | Naming style | Key permissions | Notes |
|-----------|------------|--------------|-----------------|-------|
| `Super Administrator` | `RoleSeeder`, `config/permission.super_admin_role` | Title Case | **all** (via `Gate::after`) | Implicit god-mode; never shown in role options; cannot be deleted |
| `Administrator` | `RoleSeeder`, `UserAndPermissionSeeder` | Title Case | all wildcard `*.*` + all `system.*` | Protected: cannot rename/delete (`RolePolicy`, `Role::isAdminRole`) |
| `Author` | `RoleSeeder`, `UserAndPermissionSeeder` | Title Case | `system.dashboard` | Backend access, minimal |
| `Performer` | `RoleSeeder`, `UserAndPermissionSeeder` | Title Case | `payment.management`, `public_page.profile` | Frontend performer; gets public page |
| `city_administrator` | `CityAdministratorSeeder` (⚠ not in DatabaseSeeder) | **snake_case** | `system.dashboard`, `city.manage_events`, `city.view_reports`, `product.add` | Newest role; site-scoped via `city_user`; the best template for a new role |

### 5.2 Where role names are referenced (hardcoded hotspots)

| Location | Literal(s) |
|----------|-----------|
| `config/permission.php` | `'Super Administrator'`, `'Administrator'`, `'Performer'`, `'Administrator|Super Administrator'` |
| `app/Models/User.php` | `'city_administrator'` (in `isCityAdmin`, `isCityAdministrator`), `config('permission...')` for super/admin |
| `app/Models/Role.php` | uses config for super/admin scopes |
| `RegisteredUserController.php` | `User::role('Super Administrator')` (literal) |
| `AutomateUserCreationController::validateFomEntry` | `config('permission.admin_or_super_admin')` split on `|` |
| `database/seeders/CityAdministratorSeeder.php` | `'city_administrator'` + city permissions |
| `RoleSeeder.php` | the 4 core role literals |
| `routes/admin.php` | `can:system.dashboard`, `can:system.theme`, etc. |

### 5.3 Permission catalog (`PermissionSeeder`)

Wildcard + granular per resource: `page.*`, `post.*`, `category.*`, `media.*` (+`media.other_users`), `user.*`, `error_log.*` (each with `browse/read/edit/add/delete`), plus singletons: `system.dashboard`, `system.language`, `system.translation`, `system.theme`, `system.payment`, `system.log`, `system.cookie_consent`, `payment.management`, `public_page.profile`.

Added outside the main seeder by `CityAdministratorSeeder`: `city.manage_events`, `city.view_reports`, `product.add`. The FormBuilder module references `form_builder.automate_user_creation` (registered by the module).

> `enable_wildcard_permission = true` means `page.*` grants all `page.<verb>` checks. A new role can be granted broad access with a wildcard or scoped with granular permissions.

### 5.4 Enforcement points

1. **Route middleware** — `can:system.dashboard` gates the entire `/admin` group; specific routes add `can:<perm>` or `can:<policyMethod>,Model`.
2. **Policies** — `authorizeResource()` in `UserController`/`RoleController`; `BasePermissionPolicy` maps verbs to `<base>.<verb>`; `RolePolicy` restricts to Super Admin.
3. **`Gate::after`** — Super Administrator bypasses all checks (`AuthServiceProvider`).
4. **Model scopes** — `scopeBackend` (`system.dashboard`), `scopeInRoleNames`, `scopeHasPermissionNames`, `scopeAvailable`.
5. **Login gate** — `AuthenticateLoginAttempt` requires `system.dashboard` on the admin login route.
6. **`@can` / shared Inertia props** — frontend hides UI by permission.

---

## 6. Approval Flow Details

### 6.1 The real approval gate (FormBuilder "Automate User Creation")

1. A public visitor submits an application form (FormBuilder) → a `FormEntry` row is created (no user yet, `automate_user_creation_at` empty).
2. Admin opens `GET /admin/form-builders/{form}/entries/{entry}` (`FormEntryController@show`). The page exposes `can.automate_user_creation` from `FormEntryPolicy::automateUserCreation`, which requires:
   - entry not soft-deleted,
   - `can('form_builder.automate_user_creation')`,
   - `automate_user_creation_at` empty (not already processed),
   - all mandatory user fields (`email`, `first_name`, `last_name`) are mapped.
3. Admin clicks **Create/Update user** → `AutomateUserCreationController@createOrUpdateUser`:
   - `validateFomEntry` rejects emails belonging to an `Administrator`/`Super Administrator` (`validation.email_belongs_to_protected_user`).
   - In a DB transaction, `AutomateUserCreationService::createOrUpdate`:
     - `createOrUpdateUser`: find by email or `User::factory()->make([...])` with a random password + default language; set names; save.
     - `assignRole($user, $roleId)`: detaches existing roles, assigns the **role configured on the form's mapping rule** (`group='role'`, `to.role`). If no role configured → roles detached (user left role-less).
     - `updateProfilePhoto` (if mapped), `syncUserMetas` (maps form fields → user metas, handles files/phone/translations).
   - `markAutomateActionIsDone` sets `automate_user_creation_at = now()` (marks entry processed/"approved").
   - Optionally queues `AutomateUserCreationEmail` (new) / `AutomateUserUpdateEmail` (existing) if enabled in the form settings.
   - Marks the entry read.

> ⚠️ **No email verification is performed here.** New users created via this path have `email_verified_at = null` unless mapped/handled elsewhere → they will hit the `verified` middleware on login.

### 6.2 Self-registration (no approval, no role)

`POST /register` → `CreateNewUser` creates an **unverified, role-less** user and fires `Registered` (→ verification email). `RegisteredUserController` additionally emails the Super Admin (`NewUserRegisteredNotification`). The user has **no role and thus no permissions** until an admin assigns one (via `/admin/users` or the form flow). This is the closest thing to a "pending" state, but it is not modeled explicitly.

### 6.3 Admin direct creation (immediate approval)

`POST /admin/users` (`UserController@store`): creates the user, hashes password, `saveDefaultMetas`, **auto-verifies email** (`verifiyEmail()`), assigns the chosen role, and syncs `cities` if provided. This is a one-step "create + approve + role".

### 6.4 Suspension (approval revocation)

`POST /admin/users/suspend/{user}` / `unsuspend/{user}` → `User::suspend()/unsuspend()` toggles `is_suspended`. `CheckSuspended` middleware force-logs-out suspended users on their next request. Authorized by `UserPolicy::suspend/unsuspend` (must be deletable + correct current state).

### 6.5 Role assignment locations (consolidated)

| Where | How |
|-------|-----|
| `UserController@store` | `$user->assignRole($request->role)` (role id) + `adminCities()->sync($request->cities)` |
| `UserController@update` | detach + `assignRole` (skipped entirely for Super Admins) |
| `AutomateUserCreationService::assignRole` | detach + `assignRole($roleId)` from form mapping |
| Seeders | `assignRole('Super Administrator')`, `assignRole('Administrator')`, `CityAdministratorSeeder` `givePermissionTo` |
| `Api/CityUserController@update` | sets `city_user` (branch assignment, not the role itself) |

---

## 7. Refactor Risks & Recommendations

### 7.1 Risks / smells found

| # | Risk | Location | Impact on adding a role |
|---|------|----------|-------------------------|
| R1 | **No explicit approval state** — "approved/active" is implicit across `email_verified_at`, `is_suspended`, role presence | `User` model, middleware | A new role inherits the same ambiguity; "pending" can't be expressed |
| R2 | **`CityAdministratorSeeder` not registered** in `DatabaseSeeder` | `database/seeders/DatabaseSeeder.php` | Fresh installs lack `city_administrator`; copying this template would repeat the bug |
| R3 | **Two role naming conventions** (Title Case vs snake_case) | `RoleSeeder` vs `CityAdministratorSeeder` | New role name style must be chosen deliberately; mismatches break literal comparisons |
| R4 | **Hardcoded role/permission strings** scattered across controllers/policies/model/routes | see §5.2 | Easy to miss a spot; checks like `hasRole('city_administrator')` are brittle |
| R5 | **FormBuilder approval skips email verification** | `AutomateUserCreationService::createOrUpdateUser` | New role's users may be blocked by `verified` middleware unexpectedly |
| R6 | **Role-less users are silently inert** (no permissions, no public page, can still log in) | registration path | A new role that's never assigned = dead accounts |
| R7 | **Duplicated role-assignment logic** (detach+assign) in 3+ places with slightly different rules | UserController, AutomateUserCreationService | Behavior drift; a new role may behave differently per entry point |
| R8 | **`Gate::after` super-admin bypass** uses literal `'Super Administrator'` (not config) | `AuthServiceProvider` | If role name ever changes, bypass silently breaks |
| R9 | **Branch/site assignment coupled only to City Admin** via `city_user` + literal `'city_administrator'` checks | `User::isCityAdmin` | A new site-scoped role needs its own assignment wiring or generalization |
| R10 | **`RoleController` create/edit name allows arbitrary role names** but the app expects canonical names for behavior | `RoleController`, `RoleRequest` | UI-created roles won't get code-level behavior (permissions only) |
| R11 | **`getRoleOptions` variants differ** (`withoutSuperAdmin` vs `withoutAdmin`) | `UserService` vs `AutomateUserCreationService` | A new role may or may not appear depending on entry point |

### 7.2 Recommended refactor points (before adding the role)

1. **Centralize role identity.** Move every role literal to `config/permission.php` (extend `role_names`) and an `enum` (e.g. `app/Enums/UserRole.php`). Replace literals in `User`, `RegisteredUserController`, `AuthServiceProvider` (`Gate::after`), `CityAdministratorSeeder`, and route/policy checks with the enum/config.
2. **Register all role seeders consistently.** Add `CityAdministratorSeeder` (and any new role seeder) to `DatabaseSeeder`, or create a single idempotent `RolesAndPermissionsSeeder` that defines roles → permissions in one data-driven map (`firstOrCreate` everywhere). Standardize the naming convention.
3. **Make the role→permission mapping declarative.** Replace the imperative logic in `UserAndPermissionSeeder::setPermissionToRoles` + `CityAdministratorSeeder` with a single `[role => permissions[]]` array so adding a role = one entry.
4. **Unify role assignment.** Extract a `UserRoleService::assignRole(User, roleId)` (detach + assign + `forgetCachedPermissions`) used by `UserController@store/update` and `AutomateUserCreationService`. This removes R7's drift.
5. **Decide the approval model explicitly (optional but recommended).** If the refactor needs a true "pending/approved" state, add a `status` column or `approved_at` to `users` rather than overloading verification/suspension. At minimum, fix R5 by verifying (or intentionally not verifying) email in the FormBuilder path.
6. **Generalize branch/site scoping** if the new role is site-scoped: rename `city_user`/`adminCities` concepts to a role-agnostic "user site assignment", or document that the new role reuses `city_user`.
7. **Align `getRoleOptions` behavior** so the new role shows up consistently in admin create/edit and form-mapping dropdowns.

---

## 8. Checklist for Adding a New Role

Use `city_administrator` as the working template, but fix R2/R3 along the way.

**A. Define the role & permissions**
- [ ] Choose a **canonical name and naming convention** (recommend snake_case slug like `city_administrator`, or follow Title Case if you align with the core roles). Add it to `config/permission.php` (`role_names`) and, ideally, a `UserRole` enum.
- [ ] Decide its **permission set**. Reuse existing permissions where possible; add new ones to `PermissionSeeder` (and grant via the role→permission map). Remember `enable_wildcard_permission` allows `resource.*`.
- [ ] If the role needs **backend/admin access**, it must include `system.dashboard` (otherwise admin login + `/admin` group are blocked).

**B. Seed it (make it reproducible)**
- [ ] Create/extend a seeder that does `Role::firstOrCreate(['name'=>..., 'guard_name'=>'web'])` and `givePermissionTo([...])`.
- [ ] **Register that seeder in `database/seeders/DatabaseSeeder.php`** (avoid R2).
- [ ] Add a migration or seeder for any new permissions so existing environments get them.

**C. Wire enforcement**
- [ ] Add `can:<permission>` middleware to any new routes, or new policy methods extending `BasePermissionPolicy`.
- [ ] If the role is site/branch-scoped, decide whether to reuse `city_user`/`adminCities()` or add a new assignment table; add `User` helper methods (mirroring `isCityAdmin`).
- [ ] Confirm `Gate::after` (Super Admin bypass) and `AuthenticateLoginAttempt` (admin gate) behave correctly for the new role.

**D. Assignment & UI**
- [ ] Ensure the role appears in `UserService::getRoleOptions` and `AutomateUserCreationService::getRoleOptions` (check `withoutSuperAdmin` vs `withoutAdmin` filters — R11).
- [ ] Confirm `UserStoreRequest::getRoleIds` allows the new role for the intended actors.
- [ ] If admins assign it directly: verify `UserController@store/update` path (and city sync if applicable).
- [ ] If assigned via application/approval: configure the Form-Builder mapping (`group='role'`) and confirm `AutomateUserCreationService::assignRole` handles it; verify email-verification behavior (R5).
- [ ] Update relevant Vue pages (`User/Create.vue`, `User/Edit.vue`, role filters, and any role-specific UI like `CitySelect.vue`).

**E. Profile / public page (if performer-like)**
- [ ] If the role should have a public profile, grant `public_page.profile`; optionally add a `profile-<role-slug>.blade.php` view (the public profile controller resolves views by role slug).

**F. Tests & verification**
- [ ] Add a role-permission test under `tests/Feature/RolePermission/` (mirror `UserPermissionTest`, `CityAdministratorTest`).
- [ ] Verify: login, admin access (or denial), permission-gated routes, role appears in dropdowns, assignment via all relevant paths, suspension/verification behavior.
- [ ] Run the seeders on a fresh DB to confirm the role + permissions exist.

---

## Appendix — Quick reference: function/module call sequence

**Admin creates user (one-step approval):**
```
POST /admin/users
 → UserController@store (authorizeResource: user.add)
   → UserStoreRequest::rules (role ∈ allowed ids)
   → User::factory()->create()
   → User::savePassword() → UserService::hashPassword()
   → User::saveDefaultMetas() → IPService::getCountryCode()
   → User::verifiyEmail()
   → User::assignRole($role)            [HasRoles → model_has_roles]
   → User::adminCities()->sync($cities) [city_user]  (if city admin)
```

**Application → approval (form flow):**
```
POST /admin/form-builders/{form}/entries/{entry}/automate-user-creation
 → FormEntryPolicy::automateUserCreation (gate)
 → AutomateUserCreationController@createOrUpdateUser
   → validateFomEntry (block protected admin emails)
   → AutomateUserCreationService::createOrUpdate
       → createOrUpdateUser (find/make user)
       → assignRole(user, mappedRoleId)  [detach + assignRole]
       → updateProfilePhoto / syncUserMetas
   → markAutomateActionIsDone (automate_user_creation_at = now)
   → (optional) Mail::queue(AutomateUserCreationEmail)
```

**Every authenticated request:**
```
auth:sanctum → verified (email_verified_at)
 → ensureLoginFrom{Admin}LoginRoute (portal)
 → CheckSuspended (is_suspended)
 → can:<permission> (role → role_has_permissions)  [Gate::after: Super Admin bypass]
```

*End of map.*
