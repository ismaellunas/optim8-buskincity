# New Role Implementation Checklist

This document provides a step-by-step checklist for adding a new role to the BuskinCity platform. It is based on the [Role Creation Guide](ROLE_CREATION_AND_RBAC_GUIDE.md) and [Codebase Documentation](CODEBASE_DOCUMENTATION.md).

## Phase 1: Planning & Definition

- [ ] **Define Role Name**: Determine the internal name (e.g., `city_administrator`) and display name (e.g., "City Administrator").
- [ ] **Define Scope**: Is this a global role (like "Super Admin") or a scoped role (like "City Admin" for a specific city)?
- [ ] **List Permissions**: Identify all specific actions this role needs (e.g., `city.manage_events`, `reports.view`). Check `config/permission.php` for existing naming conventions.

## Phase 2: Database & Backend (Core)

### 1. Create/Update Seeders
- [ ] **Create Seeder**: Create a new seeder file (e.g., `php artisan make:seeder CityAdminRoleSeeder`) OR update `database/seeders/RoleSeeder.php`.
- [ ] **Define Role**: In the seeder, use `Role::firstOrCreate(['name' => 'Role Name', 'guard_name' => 'web']);`.
- [ ] **Define Permissions**: Create any new permissions using `Permission::firstOrCreate(['name' => 'permission.name', 'guard_name' => 'web']);`.
- [ ] **Assign Permissions**: Link them: `$role->givePermissionTo($permissions);`.
- [ ] **Run Seeder**: Execute `php artisan db:seed --class=CityAdminRoleSeeder`.

### 2. Scoped Role Setup (Skip if Global Role)
- [ ] **Create Migration**: Run `php artisan make:migration create_[scope]_user_table` (e.g., `create_city_user_table`).
- [ ] **Define Schema**: Add `user_id` and `[scope]_id` foreign keys to the pivot table.
- [ ] **Run Migration**: `php artisan migrate`.
- [ ] **Update User Model**: In `App\Models\User.php`:
    - [ ] Add the relationship (e.g., `public function adminCities()`).
    - [ ] Add a helper method (e.g., `public function isCityAdmin($cityId)`).

### 3. Authorization Logic
- [ ] **Update Policies**: In relevant policies (e.g., `App\Policies\EventPolicy.php`):
    - [ ] Add checks for the new role.
    - [ ] If scoped, use the helper method (e.g., `$user->isCityAdmin($event->city_id)`).
- [ ] **Check Gates**: Ensure `AuthServiceProvider` logic doesn't accidentally block or grant excessive permissions (Super Admin usually bypasses this).

## Phase 3: Frontend & UI

### 1. Admin Panel Updates
- [ ] **Role Management**: Verify the new role appears in the Admin > Roles list (usually automatic if seeded).
- [ ] **User Assignment**:
    - [ ] Go to Admin > Users > Edit.
    - [ ] Verify you can assign the new role to a user.
- [ ] **Scope Assignment UI** (For Scoped Roles only):
    - [ ] Modify `resources/js/Pages/User/Edit.vue` (or create a new component).
    - [ ] Add a multi-select or search field to select the scope (e.g., Cities).
    - [ ] Update `UserController` to handle saving this relationship.

### 2. Navigation & Access
- [ ] **Menu Visibility**: Update `resources/js/Layouts/AppLayout.vue` or sidebar components to show/hide menu items based on the new role/permissions.
    - Use `$page.props.auth.user.can['permission.name']` or similar checks.

## Phase 4: Testing & Verification

### 1. Automated Tests
- [ ] **Create Feature Test**: Create `tests/Feature/RolePermission/[RoleName]Test.php`.
- [ ] **Test Permissions**: Verify the role CAN perform allowed actions.
- [ ] **Test Restrictions**: Verify the role CANNOT perform forbidden actions.
- [ ] **Test Scope**: (If scoped) Verify the role cannot access resources outside their scope.

### 2. Manual Verification
- [ ] **Login Test**: Login as a user with the new role.
- [ ] **Access Check**: Try to access a page they should see.
- [ ] **Block Check**: Try to access a page they should NOT see (verify 403 Forbidden).
- [ ] **Action Check**: Perform a Create/Update/Delete action.

## Phase 5: Documentation

- [ ] **Update Docs**: Add the new role description to `CODEBASE_DOCUMENTATION.md` under the "User Roles" section.
- [ ] **Commit**: Commit all changes, including the new seeder and migration files.
