<?php

namespace Tests\Feature\User;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateTest extends TestCase
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

    private function generateStoreInputs(): array
    {
        $password = $this->faker->password(8).'(T0T)/!';
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $password,
            'password_confirmation' => $password,
        ];
    }

    /**
     * @test
     */
    public function storeCanProduceANewUser()
    {
        // Arrange
        $inputs = $this->generateStoreInputs();

        // Act
        $response = $this->post(
            route('admin.users.store'),
            $inputs
        );

        // Assert
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', collect($inputs)->only('email')->all());
    }

    /**
     * @test
     */
    public function providingRoleWillCreateUserWithRoleAssigned()
    {
        // Arrange
        $role = Role::factory()->create();

        $inputs = array_merge(
            $this->generateStoreInputs(),
            ['role' => $role->id]
        );

        // Act
        $response = $this->post(
            route('admin.users.store'),
            $inputs
        );

        $createdUser = User::whereEmail($inputs['email'])->first();

        // Assert
        $response->assertSessionHasNoErrors();

        $this->assertTrue($createdUser->hasRole($role));
    }
}
