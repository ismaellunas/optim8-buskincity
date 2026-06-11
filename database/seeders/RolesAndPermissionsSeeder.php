<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;

/**
 * Single, idempotent source of truth for the canonical role -> permission map.
 *
 * Safe to run repeatedly and alongside the existing Role/Permission/UserAndPermission
 * seeders: it uses firstOrCreate + (idempotent) givePermissionTo and never removes
 * anything. Its primary jobs are (1) guaranteeing every canonical role exists on a
 * fresh install — including `city_administrator`, whose previous seeder was never
 * registered in DatabaseSeeder — and (2) centralizing the role->permission mapping.
 *
 * Super Administrator is intentionally not granted explicit permissions: it bypasses
 * all gates via the `Gate::after` rule in AuthServiceProvider.
 *
 * NOTE: Module-owned wildcard permissions (e.g. `product.*`, `order.*`) are seeded and
 * granted to Administrator by the module permission seeders. This seeder only expands
 * the wildcards that exist when it runs (the core set), matching the prior behavior of
 * UserAndPermissionSeeder.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    private const WILDCARDS = '__WILDCARDS__';
    private const SYSTEM = '__SYSTEM__';

    /**
     * Canonical role => permissions map.
     *
     * @return array<string, array<int, string>>
     */
    private function permissionMap(): array
    {
        return [
            UserRole::ADMINISTRATOR->value => [self::WILDCARDS, self::SYSTEM],
            UserRole::AUTHOR->value => [
                'system.dashboard',
            ],
            UserRole::PERFORMER->value => [
                'payment.management',
                'public_page.profile',
            ],
            // OQ14: City/Special-Events Admins must NOT have full dashboard access.
            // `system.dashboard` is intentionally omitted and actively revoked below.
            UserRole::CITY_ADMINISTRATOR->value => [
                'city.manage_events',
                'city.view_reports',
                'product.add',
                // Logo/cover on the assigned city's public page (Space edit).
                'media.add',
                'media.browse',
                'media.read',
                'media.edit',
            ],
            // Phase 2 (OQ1/OQ14): Special Events Admin — city-scoped, many-per-city.
            // May create/manage ONLY special-events pitches (enforced at policy level),
            // never normal pitches and never the full dashboard. The `special_events.manage`
            // permission is the forward hook refined in Phase 6 (T-SE).
            UserRole::SPECIAL_EVENTS_ADMIN->value => [
                'special_events.manage',
                'product.add',
                'public_page.profile',
            ],
        ];
    }

    public function run(): void
    {
        // 1. Ensure every canonical role exists (fixes the orphaned city_administrator).
        foreach (UserRole::values() as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 2. Apply the role -> permission map idempotently.
        foreach ($this->permissionMap() as $roleName => $permissions) {
            $role = Role::findByName($roleName, 'web');

            $expanded = $this->expandPermissions($permissions);

            // Ensure any explicit (non-pattern) permission exists before granting it.
            foreach ($expanded as $permissionName) {
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
            }

            $role->givePermissionTo($expanded);
        }

        $this->revokeStalePermissions();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Remove permissions that were granted by older seeders but must no longer
     * apply. Idempotent: a no-op once the grant is gone.
     *
     * OQ14: `city_administrator` previously received `system.dashboard`
     * (orphaned CityAdministratorSeeder); City Admins must not have dashboard
     * access, so revoke it explicitly.
     */
    private function revokeStalePermissions(): void
    {
        $cityAdmin = Role::findByName(UserRole::CITY_ADMINISTRATOR->value, 'web');

        $dashboard = Permission::where('name', 'system.dashboard')
            ->where('guard_name', 'web')
            ->first();

        if ($dashboard && $cityAdmin->hasPermissionTo($dashboard)) {
            $cityAdmin->revokePermissionTo($dashboard);
        }
    }

    /**
     * Resolve pattern tokens (wildcards / system permissions) against the
     * permissions that currently exist, leaving explicit names untouched.
     *
     * @param  array<int, string>  $permissions
     * @return array<int, string>
     */
    private function expandPermissions(array $permissions): array
    {
        $resolved = new Collection();

        foreach ($permissions as $permission) {
            if ($permission === self::WILDCARDS) {
                $resolved = $resolved->merge(
                    Permission::where('name', 'LIKE', '%.*')->pluck('name')
                );
            } elseif ($permission === self::SYSTEM) {
                $resolved = $resolved->merge(
                    Permission::where('name', 'LIKE', 'system.%')->pluck('name')
                );
            } else {
                $resolved->push($permission);
            }
        }

        return $resolved->unique()->values()->all();
    }
}
