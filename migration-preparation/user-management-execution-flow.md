# User Management — Complete Execution Flow

> Generated: 2026-04-09  
> Purpose: Migration preparation — full trace of user management flows from route to database, including logic for CRUD, roles, profile management, and resource ownership.

---

## 1. Admin User CRUD (`UserController`)

Managed via `Route::resource('admin.users', UserController::class)` and `UserService`.

### Flow: Listing Users (`GET /admin/users`)
1. **Route**: `admin.users.index`
2. **Controller**: `UserController@index`
3. **Logic**:
    - Checks permissions: `user.add`, `user.delete`, `user.edit`, `system.dashboard`.
    - Calls `UserService::getRecords()` with search terms/role filters.
4. **Service**: `UserService` builds a query with:
    - `orderBy('id', 'DESC')`
    - Eager load `roles` and `roles.permissions`.
    - Dynamic scopes: `search` (name/email), `inRoles`.
    - **Key Decision**: If actor is not Super Admin, filters out Super Admin users from the list.
5. **Response**: Inertia render `User/Index` with paginated records and `roleOptions`.

### Flow: Creating User (`POST /admin/users`)
1. **Request Validation**: `UserStoreRequest` (Name, Email, Password, Role, Language).
2. **Controller**: `UserController@store`
3. **Execution Steps**:
    - Creates User via factory/eloquent.
    - `savePassword()`: Hashes via `UserService`.
    - `saveDefaultMetas()`: Seeds initial meta (e.g. `country` from IP lookup).
    - `verifiyEmail()`: Manually sets `email_verified_at` (skipping standard verification loop).
    - **Photo**: If present, calls `updateProfilePhoto()` → `MediaService::uploadProfile()`.
    - **Roles**: `assignRole($request->role)`.
    - **Cities**: If `cities` present, syncs `city_user` pivot table.
4. **Side Effects**:
    - Media record created in `media` table.
    - Meta records created in `user_metas` table.

### Flow: Deleting User (`DELETE /admin/users/{user}`)
1. **Controller**: `UserController@destroy`
2. **Logic Check**: `request->is_reassigned`
    - **Scenario A (Reassign)**: Calls `UserService::reassignResources(from, to)`.
        - Updates `Post` and `Page` tables: `UPDATE table SET author_id = {to} WHERE author_id = {from}`.
    - **Scenario B (Clean Delete)**: Calls `UserService::deleteResources(id)`.
        - Calls `delete()` on every `Page` and `Post` owned by user (triggering their respective model events).
3. **Cleanup**: Calls `DeleteUser` action (Jetstream contract).
    - `deleteProfilePhoto()`: Removes from Cloudinary and DB.
    - `tokens->each->delete()`: Revokes API tokens.
    - `connectedAccounts->each->delete()`: Removes OAuth links.
    - `$user->delete()`: Fires Eloquent `deleted` event (SoftDelete).

---

## 2. Role & Permission Management (`RoleController`)

Managed via `spatie/laravel-permission` with custom UI.

### Flow: Update Role (`PUT /admin/roles/{role}`)
1. **Controller**: `RoleController@update`
2. **Logic**:
    - **Name**: Only updates if user has `editName` permission (prevents renaming protected roles).
    - **Permissions**: `syncPermissions($request->permissions)`.
3. **Service Logic**: `RoleService::getPermissionOptions()`
    - Groups permissions by "Module" and "GroupTitle" (e.g., `Booking.Orders`).
    - **Hidden Dependency**: Calls `ModuleService` to only show permissions for *enabled* modules.
4. **Database**: Updates `roles` table and `role_has_permissions` pivot.

---

## 3. Profile Management (`UserProfileController`)

Handles the current user's profile settings (Inertia/Jetstream).

### Flow: Show Profile (`GET /user/profile` or `/admin/profile`)
1. **Controller**: `UserProfileController@show` (Custom, extends Jetstream).
2. **Decision**: Sets `pageComponent` to `Profile/ShowFrontend` or `Profile/ShowAdmin` based on route.
3. **Logic**:
    - Injects `socialiteDrivers` if OAuth is enabled.
    - Injects `sessions` (standard Jetstream browser session tracking).
    - Shares `profilePageUrl` only if `user->hasPublicPage` is true.

### Flow: User Update Profile (`POST /user/profile-information`)
1. **Action**: `UpdateUserProfileInformation@update` (Fortify).
2. **Execution**:
    - Updates `first_name`, `last_name`, `email`.
    - **Side Effect**: If email changed, sets `email_verified_at = null` and sends `VerificationNotification` (if `advanceUpdateProfile` flag is true).
    - **Photo**: If `photo` present, calls `$user->updateProfilePhoto($photo)`.

---

## 4. Public Profile Page (`Frontend\ProfileController`)

Public-facing performer profile.

### Flow: View Public Profile (`GET /profile/{user:unique_key}`)
1. **Route Binding**: Uses `unique_key` column instead of ID.
2. **Execution**:
    - Resolves `$user->roleName`.
    - **View Selection**: Attempts to load a view named `profile-{role-slug}`.
        - e.g., `profile-street-performer.blade.php`.
        - Fallback: `profile.blade.php`.
    - **QR Code**: Injects QR code configuration (logo URL, name) if enabled in settings.

---

## 5. Manual Password Reset Flow (`SendUserPasswordResetEmailController`)

Triggered by Admin for one or more users.

### Execution Path
1. **Controller**: `SendUserPasswordResetEmailController` (Invokable).
2. **Broker**: Uses `Password::broker('users:bulk')`.
3. **Callback**: `sendResetLinkCallback`
    - Fires notification `UserPasswordResetLink`.
    - **Custom Subject/Content**: Admin can provide custom email text for this specific batch.
4. **Side Effect**: Standard Laravel `password_resets` table insert + queued email.

---

## Hidden Dependencies & Side Effects

| Action | Hidden Dependency | Side Effect |
|--------|-------------------|-------------|
| **User Listing** | `SettingService` (records per page) | None |
| **User Create/Update** | `LanguageService` (default ID) | `user_metas` seeded with country |
| **User Delete** | `DeleteUser` action (Jetstream) | `media` file deletion on Cloudinary |
| **User Set Language** | `TranslationService` | Changes app locale UI on next load |
| **Login as Admin** | `SetClientAuthToken` middleware | Sets two browser cookies for JS auth |
| **Role Sync** | `RoleObserver` | Clears permission cache in Redis/DB |

---

## Database Trace (Critical Tables)

| Table | Usage |
|-------|-------|
| `users` | Primary user record; soft deletes. |
| `user_metas` | Arbitrary JSON/string meta (Country, Profile data). |
| `roles` / `permissions` | Spatie RBAC tables. |
| `connected_accounts` | Socialstream OAuth tokens. |
| `city_user` | Pivot table for City Administrators. |
| `media` | Profile photo records (Cloudinary versioning). |
| `password_resets` | Manual and automated reset tokens. |

---

*End of User Management Execution Flow*
