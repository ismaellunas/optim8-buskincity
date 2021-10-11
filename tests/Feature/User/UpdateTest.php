<?php

namespace Tests\Feature\User;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        // Arrange
        $this->user = User::factory()->create();

        $this->actingAs($this->user);

        $this->user->assignRole(config('permission.super_admin_role'));
    }

    private function generateUpdateInputs(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
        ];
    }

    /**
     * @test
     */
    public function userProfilesCanUpdated()
    {
        // Arrange
        $user = User::factory()->create();

        $inputs = $this->generateUpdateInputs();

        // Act
        $response = $this->put(
            route('admin.users.update', $user->id),
            $inputs
        );

        // Assert
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $inputs['email'],
            'name' => $inputs['name'],
        ]);
    }

    /**
     * @test
     */
    public function provideRoleCanChangeAssignedRole()
    {
        // Arrange
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $newRole = Role::factory()->create();

        // Act
        $user->assignRole($role);

        $response = $this->put(
            route('admin.users.update', $user->id),
            [
                'email' => $user->email,
                'name' => $this->faker->name(),
                'role' => $newRole->id,
            ]
        );

        $user->fresh();

        // Assert
        $response->assertSessionHasNoErrors();

        $this->assertTrue($user->hasRole($role));
        $this->assertFalse($user->hasRole($newRole));
    }
}
