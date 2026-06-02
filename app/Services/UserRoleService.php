<?php

namespace App\Services;

use App\Models\User;

/**
 * Centralizes single-role assignment so every entry point (admin user CRUD,
 * FormBuilder automated user creation, etc.) applies identical semantics.
 *
 * The application treats users as having at most one role, so assigning a role
 * always detaches existing roles first. Callers remain responsible for any
 * authorization checks (e.g. not mutating a Super Administrator) before calling.
 */
class UserRoleService
{
    /**
     * Ensure the user has exactly the given role (or no role when null/empty).
     *
     * Accepts a role name (string) or role id (int); both are understood by the
     * underlying spatie/laravel-permission methods.
     *
     * @param  string|int|null  $role
     */
    public function syncSingleRole(User $user, $role, bool $forgetCache = true): void
    {
        if (blank($role)) {
            $user->roles()->detach();
        } elseif (! $user->hasRole($role)) {
            $user->roles()->detach();
            $user->assignRole($role);
        }

        if ($forgetCache) {
            $user->forgetCachedPermissions();
        }
    }
}
