<?php

namespace Tests\Feature\RolePermission;

use App\Models\User;

class UserPermissionTest extends BaseRolePermissionTestCase
{
    protected $basePermissionName = 'user';
    protected $baseRouteName = 'admin.users';

    private function generateUpdateInputs(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
        ];
    }

    private function generateStoreInputs(): array
    {
        $password = $this->faker->password(8).'(T0T)/!';
        return array_merge(
            $this->generateUpdateInputs(),
            [
                'password' => $password,
                'password_confirmation' => $password,
            ]
        );
    }


    /**
     * @test
     */
    public function indexCanBeAccessedByUserWithUserBrowsePermission()
    {
        // Act
        $this->givePermissionToRole('browse');

        $response = $this->get(route($this->baseRouteName.'.index'));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function indexCannotBeAccessedByUserWhoHasNoUserBrowsePermission()
    {
        // Act
        $this->revokePermissionToRole('browse');

        $response = $this->get(route($this->baseRouteName.'.index'));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function editCanBeAccessedByUserWithUserEditPermission()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $this->givePermissionToRole('edit');

        $response = $this->get(route($this->baseRouteName.'.edit', $user->id));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function editCannotBeAccessedByUserWhoHasNoUserEditPermission()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $this->revokePermissionToRole('edit');

        $response = $this->get(route($this->baseRouteName.'.edit', $user->id));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function updateCanBeAccessedByUserWithUserEditPermission()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $this->givePermissionToRole('edit');

        $response = $this->put(
            route($this->baseRouteName.'.update', $user->id),
            $this->generateUpdateInputs()
        );

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function updateCannotBeAccessedByUserWhoHasNoUserEditPermission()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $this->revokePermissionToRole('edit');

        $response = $this->put(route($this->baseRouteName.'.update', $user->id), []);

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function createCanBeAccessedByUserWithUserAddPermission()
    {
        // Act
        $this->givePermissionToRole('add');

        $response = $this->get(route($this->baseRouteName.'.create'));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function createCannotBeAccessedByUserWhoHasNoUserAddPermission()
    {
        // Act
        $this->revokePermissionToRole('add');

        $response = $this->get(route($this->baseRouteName.'.create'));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function storeCanBeAccessedByUserWithUserAddPermission()
    {
        // Act
        $this->givePermissionToRole('add');

        $response = $this->post(
            route($this->baseRouteName.'.store'),
            $this->generateStoreInputs()
        );

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function storeCannotBeAccessedByUserWhoHasNoUserAddPermission()
    {
        // Act
        $this->revokePermissionToRole('add');

        $response = $this->post(route($this->baseRouteName.'.store'), []);

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function deleteCanBeDoneByUserWithUserDeletePermission()
    {
        // Arrange
        $this->givePermissionToRole('delete');

        $user = User::factory()->create();

        // Act
        $response = $this->delete(route($this->baseRouteName.'.destroy', [$user->id]));

        // Assert
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function deleteCannotBeDoneByUserWhoHasNoDeletePermission()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $this->revokePermissionToRole('delete');

        $response = $this->delete(route($this->baseRouteName.'.destroy', [$user->id]));

        // Assert
        $response->assertForbidden();
    }
}
