<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Spatie\Permission\PermissionRegistrar;

/**
 * Ensure role/permission relations are fresh immediately after login.
 *
 * Without this, the first post-login request can resolve scoped listings against
 * stale Spatie cache or unloaded relations, then succeed on a full refresh.
 */
class RefreshAuthenticatedUserPermissions
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! method_exists($user, 'unsetRelation')) {
            return;
        }

        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (method_exists($user, 'load')) {
            $user->load(['roles', 'permissions']);
        }
    }
}
