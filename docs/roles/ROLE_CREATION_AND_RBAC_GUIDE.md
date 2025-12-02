# Role Creation and RBAC Architecture Guide

This document provides a technical overview of the Role-Based Access Control (RBAC) system in the application and serves as a guide for developers adding new roles. It specifically details the architecture and provides a case study on implementing a "City Administrator" role.

## 1. Architecture Overview

The application utilizes **Spatie Laravel-Permission** for managing roles and permissions, extended with custom logic for application-specific needs.

### Core Components

*   **Models**:
    *   `App\Models\Role`: Extends `Spatie\Permission\Models\Role`. Includes scopes like `withoutSuperAdmin` and logic to protect the "Super Administrator" role.
    *   `App\Models\Permission`: Extends `Spatie\Permission\Models\Permission`.
    *   `App\Models\User`: Uses the `HasRoles` trait. Includes helper accessors like `isSuperAdministrator`, `isAdministrator`, and `roleName`.

*   **Authorization Layer**:
    *   **Policies**: Most policies extend `App\Policies\BasePermissionPolicy`, which automatically maps standard CRUD actions (`viewAny`, `view`, `create`, `update`, `delete`) to permission strings (e.g., `user.browse`, `user.read`).
    *   **Gates**: A global gate in `AuthServiceProvider` grants all abilities to the "Super Administrator" role implicitly.
    *   **Middleware**: Standard Spatie middleware (`role`, `permission`, `role_or_permission`) is available.

*   **Database**:
    *   Standard Spatie tables: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`.

## 2. Existing Roles

Currently defined roles (in `RoleSeeder.php`):
1.  **Super Administrator**: Has full access to the system (bypasses checks via `AuthServiceProvider`).
2.  **Administrator**: High-level access, typically manages content and users but may be restricted from critical system settings.
3.  **Author**: Content creator.
4.  **Performer**: Specific to the business logic (likely for events/performances).

## 3. How to Add a New Role

To add a standard role (global scope), follow these steps:

### Step 1: Define Role and Permissions
Identify the role name and the specific permissions it requires. Check `config/permission.php` or `PermissionSeeder.php` for existing permission patterns (e.g., `resource.action`).

### Step 2: Create/Update Seeders
You should use seeders to ensure roles and permissions exist in all environments.

**Option A: Update Existing Seeders**
*   Add the role to `database/seeders/RoleSeeder.php`.
*   Add new permissions to `database/seeders/PermissionSeeder.php`.

**Option B: Create Dedicated Seeder (Recommended for new features)**
Create a new seeder, e.g., `php artisan make:seeder CityAdminSeeder`.

```php
use App\Models\Role;
use App\Models\Permission;

public function run()
{
    // 1. Create Role
    $role = Role::firstOrCreate(['name' => 'City Administrator', 'guard_name' => 'web']);

    // 2. Create Permissions (if new)
    $permissions = [
        'city.manage_events',
        'city.view_reports',
    ];

    foreach ($permissions as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    // 3. Assign Permissions
    $role->givePermissionTo($permissions);
}
```

### Step 3: Run Seeders
Run `php artisan db:seed --class=CityAdminSeeder`.

## 4. Case Study: Implementing "City Administrator"

The "City Administrator" role is unique because it is **scoped**. A user isn't just a "City Administrator" globally; they are an administrator *for a specific city*.

### Challenge
Standard RBAC roles are global. `User::hasRole('City Administrator')` is true regardless of which city is being accessed.

### Solution Architecture
We combine the standard Role (for capabilities) with a Many-to-Many relationship (for scope).

#### 1. Database Changes
We need a pivot table to link Users to Cities.

```bash
php artisan make:migration create_city_user_table
```

**Migration:**
```php
Schema::create('city_user', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('city_id')->constrained()->cascadeOnDelete(); // Assuming 'cities' table exists
    $table->timestamps();
});
```

#### 2. Model Relationships (`App\Models\User`)
Add the relationship and a helper method to check the scope.

```php
// App\Models\User.php

public function adminCities()
{
    return $this->belongsToMany(City::class, 'city_user');
}

/**
 * Check if user is a City Admin for a specific city.
 */
public function isCityAdmin(int $cityId): bool
{
    // Check if they have the role AND are assigned to the specific city
    return $this->hasRole('City Administrator') && 
           $this->adminCities()->where('city_id', $cityId)->exists();
}
```

#### 3. Policy Implementation
In your Policies (e.g., `EventPolicy`), use the scoped check instead of just `can()`.

```php
// App\Policies\EventPolicy.php

public function update(User $user, Event $event)
{
    // Allow Super Admin
    if ($user->isSuperAdministrator) {
        return true;
    }

    // Allow City Admin if the event belongs to their city
    if ($user->isCityAdmin($event->city_id)) {
        return true;
    }

    return false;
}
```

#### 4. Admin UI Updates
To manage this role, the Admin User Interface needs to be updated:
1.  **Role Assignment**: Assign the "City Administrator" role in the existing Roles UI.
2.  **Scope Assignment**: Add a new UI component (e.g., a multi-select dropdown) in the User Edit page to select which Cities the user manages. This data syncs to the `city_user` table.

### Summary of City Administrator Implementation Steps
1.  **Create Role**: Run seeder to add 'City Administrator'.
2.  **Create Permission**: Add `manage city events` permission.
3.  **Migration**: Create `city_user` table.
4.  **Model**: Add `adminCities()` to `User`.
5.  **Logic**: Implement `isCityAdmin($cityId)` helper.
6.  **Policy**: Update `EventPolicy` to use `isCityAdmin`.
7.  **API/UI**: Expose endpoints to attach users to cities.

## 5. Best Practices
*   **Use `BasePermissionPolicy`**: For standard CRUD resources, extend this policy to automatically handle `viewAny`, `view`, `create`, `update`, `delete` checks based on naming conventions.
*   **Avoid Hardcoding IDs**: Always refer to roles by name or config constants (e.g., `config('permission.role_names.admin')`).
*   **Seeders are Source of Truth**: Always define roles/permissions in seeders so they can be replicated in testing and production.
