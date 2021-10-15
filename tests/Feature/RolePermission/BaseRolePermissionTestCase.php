<?php

namespace Tests\Feature\RolePermission;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
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
        $this->seed(DatabaseSeeder::class);

        // Arrange
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->role = Role::factory()->create();
        $this->user->assignRole($this->role);
    }

    protected function givePermissionToRole(string $permission, string $basePermission = null)
    {
        $this->role->givePermissionTo(
            $basePermission ?? $this->basePermissionName.
            '.'.
            $permission
        );
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
