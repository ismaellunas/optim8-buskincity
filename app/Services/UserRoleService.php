<?php

namespace App\Services;

use App\Models\User;

/**
 * Centralizes single-role assignment so every entry point (admin user CRUD,
 * FormBuilder automated user creation, etc.) applies identical semantics.
 *
 * The application treats users as having at most one role, so assigning a role
 * always detaches existing roles first. City scopes that no longer match the
 * resulting role are cleared in the same write so leftover `city_user` /
 * `user_scope` rows cannot outlive a demotion. Callers remain responsible for
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

        $user->unsetRelation('roles');

        if ($forgetCache) {
            $user->forgetCachedPermissions();
        }

        $this->syncCityScopesToCurrentRole($user);
    }

    /**
     * Drop city scopes that belong to a role the user no longer holds.
     */
    private function syncCityScopesToCurrentRole(User $user): void
    {
        if (! $user->isCityAdministrator()) {
            $user->syncAdminCities([]);
        }

        if (! $user->isSpecialEventsAdmin()) {
            $user->syncScopeCities(
                config('permission.role_names.special_events_admin'),
                []
            );
        }
    }
}
