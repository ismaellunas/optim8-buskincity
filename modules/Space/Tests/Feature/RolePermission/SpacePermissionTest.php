<?php

namespace Modules\Space\Tests\Feature\RolePermission;

use Tests\Feature\RolePermission\BaseRolePermissionTestCase;
use Modules\Space\Database\Seeders\PermissionSeeder;
use Modules\Space\Entities\Space;
use Inertia\Testing\AssertableInertia as Assert;

class SpacePermissionTest extends BaseRolePermissionTestCase
{
    protected $basePermissionName = 'space';
    protected $baseRouteName = 'admin.spaces';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->givePermissionToRole('dashboard', 'system');

        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureLoginFromAdminLoginRoute::class,
            \App\Http\Middleware\UserEmailIsVerified::class,
        ]);
    }

    private function assignUserASpace(array $spaceProps = []): Space
    {
        $space = Space::factory()->create($spaceProps);

        $this->user->spaces()->sync($space);

        return $space;
    }

    /**
     * @test
     */
    public function indexCanBeAccessedByUserWithCategoryBrowsePermission()
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
    public function indexCanBeAccessedBySpaceManager()
    {
        // Assign
        $space = Space::factory()->create();

        // Act
        $space->managers()->attach($this->user);

        $response = $this->get(route($this->baseRouteName.'.index'));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function indexCannotBeAccessedWithoutBrowsePermissionOrManagedSpace()
    {
        // Act
        $this->revokePermissionToRole('browse');

        $this->user->spaces()->sync([]);

        $response = $this->get(route($this->baseRouteName.'.index'));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function editCanBeAccessedByUserWithEditPermission()
    {
        // Arrange
        $space = Space::factory()->create();

        // Act
        $this->givePermissionToRole('edit');

        $response = $this->get(route($this->baseRouteName.'.edit', $space));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function editCanBeAccessedBySpaceManager()
    {
        // Arrange
        $space = Space::factory()->create();

        // Act
        $space->managers()->attach($this->user);

        $this->revokePermissionToRole('edit');

        $response = $this->get(route($this->baseRouteName.'.edit', $space));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function editCannotBeAccessedByUserWhoHasNoSpaceEditPermissionNeitherNorManagedSpace()
    {
        // Arrange
        $space = Space::factory()->create();

        // Act
        $space->managers()->sync([]);

        $this->revokePermissionToRole('edit');

        $response = $this->get(route($this->baseRouteName.'.edit', $space));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function updateCanBeAccessedByUserWithSpaceEditPermission()
    {
        // Arrange
        $space = Space::factory()->create();

        // Act
        $space->managers()->sync([]);

        $this->givePermissionToRole('edit');

        $response = $this->put(route($this->baseRouteName.'.update', $space->id), [
            'name' => $this->faker->sentence(3),
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function updateCanBeAccessedBySpaceManager()
    {
        // Arrange
        $space = Space::factory()->create();

        // Act
        $space->managers()->sync($this->user);

        $this->revokePermissionToRole('edit');

        $response = $this->put(route($this->baseRouteName.'.update', $space->id), [
            'name' => $this->faker->sentence(3),
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function updateCannotBeAccessedByUserWhoHasNoSpaceEditPermissionNeitherNorManagedSpace()
    {
        // Arrange
        $space = Space::factory()->create();

        // Act
        $space->managers()->sync([]);

        $this->revokePermissionToRole('edit');

        $response = $this->put(route($this->baseRouteName.'.update', $space->id), [
            'name' => $this->faker->sentence(2),
        ]);

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function createCanBeAccessedByUserWithSpaceAddPermission()
    {
        // Act
        $this->user->spaces()->sync([]);

        $this->givePermissionToRole('add');

        $response = $this->get(route($this->baseRouteName.'.create'));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function createCanBeAccessedBySpaceManager()
    {
        // Arrange
        $space = Space::factory()->create(['parent_id' => null]);

        // Act
        $this->user->spaces()->sync($space);

        $this->revokePermissionToRole('add');

        $response = $this->get(route($this->baseRouteName.'.create'));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function createCannotBeAccessedByUserWhoHasNoSpaceAddPermissionNeigherNorManagedSpace()
    {
        // Act
        $this->user->spaces()->sync([]);

        $this->revokePermissionToRole('add');

        $response = $this->get(route($this->baseRouteName.'.create'));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function storeCanBeAccessedByUserWithSpaceAddPermission()
    {
        // Act
        $this->givePermissionToRole('add');

        $response = $this->post(route($this->baseRouteName.'.store'), [
            'name' => $this->faker->sentence(2),
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function storeCanBeAccessedBySpaceManager()
    {
        $space = $this->assignUserASpace();
        $space->parent_id = null;
        $space->save();

        // Act
        $this->revokePermissionToRole('add');

        $response = $this->post(route($this->baseRouteName.'.store'), [
            'name' => $this->faker->sentence(2),
            'parent_id' => $space->id,
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function storeIsFailureIfSpaceManagerDoesntProvideSpaceParent()
    {
        $space = $this->assignUserASpace();
        $space->parent_id = null;
        $space->save();

        // Act
        $this->revokePermissionToRole('add');

        $response = $this->post(route($this->baseRouteName.'.store'), [
            'name' => $this->faker->sentence(2),
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['parent_id']);
    }

    /**
     * @test
     */
    public function storeCannotBeAccessedByUserWhoHasNoSpaceAddPermission()
    {
        // Act
        $this->revokePermissionToRole('add');

        $response = $this->post(route($this->baseRouteName.'.store'), [
            'name' => $this->faker->sentence(2),
        ]);

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function storeCannotBeAccessedByUserWhoHasNoSpaceAddPermissionNeigherNorManagedSpace()
    {
        // Act
        $this->user->spaces()->sync([]);
        $this->revokePermissionToRole('add');

        $response = $this->post(route($this->baseRouteName.'.store'), []);

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function deleteCanBeDoneByUserWithSpaceDeletePermission()
    {
        // Arrange
        $this->givePermissionToRole('delete');

        $space = Space::factory()->create();

        // Act
        $response = $this->delete(route($this->baseRouteName.'.destroy', [$space->id]));

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function deleteCanBeDoneBySpaceManagerWhenDeletingChildOfManagedSpace()
    {
        // Arrange
        $space = $this->assignUserASpace();

        $spaceChild = Space::factory()->create([
            'parent_id' => $space->id,
        ]);

        // Act
        $this->revokePermissionToRole('delete');

        $response = $this->delete(route($this->baseRouteName.'.destroy', [
            $spaceChild->id
        ]));

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function deleteCannotBeDoneBySpaceManagerWhenDeletingItsManagedSpace()
    {
        // Arrange
        $space = $this->assignUserASpace();

        // Act
        $this->revokePermissionToRole('delete');

        $response = $this->delete(route($this->baseRouteName.'.destroy', [$space->id]));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function deleteCannotBeDoneByUserWhoHasNoSpaceDeletePermission()
    {
        // Arrange
        $space = Space::factory()->create();

        // Act
        $this->revokePermissionToRole('delete');

        $response = $this->delete(route($this->baseRouteName.'.destroy', [$space->id]));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function deleteCannotBeAccessedByUserWhoHasNoSpaceDeletePermissionNeigherNorManagedSpace()
    {
        // Arrange
        $space = Space::factory()->create();

        // Act
        $this->user->spaces()->sync([]);
        $this->revokePermissionToRole('add');

        $response = $this->delete(route($this->baseRouteName.'.destroy', [$space->id]));

        // Assert
        $response->assertForbidden();
    }
}
