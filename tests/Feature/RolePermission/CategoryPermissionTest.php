<?php

namespace Tests\Feature\RolePermission;

use App\Models\Category;

class CategoryPermissionTest extends BaseRolePermissionTestCase
{
    protected $basePermissionName = 'category';
    protected $baseRouteName = 'admin.categories';

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
    public function indexCannotBeAccessedByUserWhoHasNoCategoryBrowsePermission()
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
    public function editCanBeAccessedByUserWithCategoryEditPermission()
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $this->givePermissionToRole('edit');

        $response = $this->get(route($this->baseRouteName.'.edit', $category->id));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function editCannotBeAccessedByUserWhoHasNoCategoryEditPermission()
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $this->revokePermissionToRole('edit');

        $response = $this->get(route($this->baseRouteName.'.edit', $category->id));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function updateCanBeAccessedByUserWithCategoryEditPermission()
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $this->givePermissionToRole('edit');

        $response = $this->put(route($this->baseRouteName.'.update', $category->id), []);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function updateCannotBeAccessedByUserWhoHasNoCategoryEditPermission()
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $this->revokePermissionToRole('edit');

        $response = $this->put(route($this->baseRouteName.'.update', $category->id), []);

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function createCanBeAccessedByUserWithCategoryAddPermission()
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
    public function createCannotBeAccessedByUserWhoHasNoCategoryAddPermission()
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
    public function storeCanBeAccessedByUserWithCategoryAddPermission()
    {
        // Act
        $this->givePermissionToRole('add');

        $response = $this->post(route($this->baseRouteName.'.store'), []);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function storeCannotBeAccessedByUserWhoHasNoCategoryAddPermission()
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
    public function deleteCanBeDoneByUserWithCategoryDeletePermission()
    {
        // Arrange
        $this->givePermissionToRole('delete');

        $category = Category::factory()->create();

        // Act
        $response = $this->delete(route($this->baseRouteName.'.destroy', [$category->id]));

        // Assert
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function deleteCannotBeDoneByUserWhoHasNoDeletePermission()
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $this->revokePermissionToRole('delete');

        $response = $this->delete(route($this->baseRouteName.'.destroy', [$category->id]));

        // Assert
        $response->assertForbidden();
    }
}
