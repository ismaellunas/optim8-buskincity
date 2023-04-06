<?php

namespace Tests\Feature\RolePermission;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BaseRolePermissionTestCase extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $basePermissionName;
    protected $baseRouteName;
    protected $role;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(SettingSeeder::class);

        // Arrange
        $this->user = User::factory()->create();

        $this->role = Role::factory()->create();
        $this->user->assignRole($this->role);

        $this->actingAs($this->user);
    }

    protected function givePermissionToRole(string|array $permission, string $basePermission = null)
    {
        $permissionNames = [];

        if (is_string($permission)) {
            $permission = [$permission];
        }

        foreach ($permission as $permissionName) {
            $permissionNames[] = (
                (is_null($basePermission) ? $this->basePermissionName : $basePermission).
                '.'.$permissionName
            );
        }

        $this->role->givePermissionTo($permissionNames);
    }

    protected function revokePermissionToRole(string $permission, string $basePermission = null)
    {
        $this->role->revokePermissionTo(
            $basePermission ?? $this->basePermissionName.
            '.'.
            $permission
        );
    }
}
