<?php

namespace Tests\Feature\RolePermission;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Support\Str;

class PagePermissionTest extends BaseRolePermissionTestCase
{
    protected $basePermissionName = 'page';
    protected $baseRouteName = 'admin.pages';

    private function generateInputs($pageTranslation = null): array
    {
        $title = $this->faker->sentence();

        $inputs['en'] = [
            'id' => null,
            'title' => $title,
            'slug' => Str::slug($title),
            'locale' => 'en',
            'data' => [
                'structures' => [],
                'entities' => [],
                'media' => [],
            ],
        ];

        if ($pageTranslation) {
            $inputs['en']['id'] = $pageTranslation->id;
        }

        return $inputs;
    }

    /**
     * @test
     */
    public function indexCanBeAccessedByUserWithPageBrowsePermission()
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
    public function indexCannotBeAccessedByUserWhoHasNoPageBrowsePermission()
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
    public function editCanBeAccessedByUserWithPageEditPermission()
    {
        // Arrange
        $page = Page::factory()->create();

        // Act
        $this->givePermissionToRole('edit');

        $response = $this->get(route($this->baseRouteName.'.edit', $page->id));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function editCannotBeAccessedByUserWhoHasNoPageEditPermission()
    {
        // Arrange
        $page = Page::factory()->create();

        // Act
        $this->revokePermissionToRole('edit');

        $response = $this->get(route($this->baseRouteName.'.edit', $page->id));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function updateCanBeAccessedByUserWithPageEditPermission()
    {
        // Arrange
        $pageTranslation = PageTranslation::factory()->create();

        // Act
        $this->givePermissionToRole('edit');

        $response = $this->put(
            route($this->baseRouteName.'.update', $pageTranslation->page_id),
            $this->generateInputs($pageTranslation)
        );

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function updateCannotBeAccessedByUserWhoHasNoPageEditPermission()
    {
        // Arrange
        $page = Page::factory()->create();

        // Act
        $this->revokePermissionToRole('edit');

        $response = $this->put(route($this->baseRouteName.'.update', $page->id), []);

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function createCanBeAccessedByUserWithPageAddPermission()
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
    public function createCannotBeAccessedByUserWhoHasNoPageAddPermission()
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
    public function storeCanBeAccessedByUserWithPageAddPermission()
    {
        // Act
        $this->givePermissionToRole('add');

        $response = $this->post(
            route($this->baseRouteName.'.store'),
            $this->generateInputs()
        );

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function storeCannotBeAccessedByUserWhoHasNoPageAddPermission()
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
    public function deleteCanBeDoneByUserWithPageDeletePermission()
    {
        // Arrange
        $this->givePermissionToRole('delete');

        $page = Page::factory()->create();

        // Act
        $response = $this->delete(route($this->baseRouteName.'.destroy', [$page->id]));

        // Assert
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function deleteCannotBeDoneByUserWhoHasNoDeletePermission()
    {
        // Arrange
        $page = Page::factory()->create();

        // Act
        $this->revokePermissionToRole('delete');

        $response = $this->delete(route($this->baseRouteName.'.destroy', [$page->id]));

        // Assert
        $response->assertForbidden();
    }
}
