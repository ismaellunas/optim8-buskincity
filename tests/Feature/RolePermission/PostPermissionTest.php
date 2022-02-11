<?php

namespace Tests\Feature\RolePermission;

use App\Contracts\PublishableInterface;
use App\Models\Post;
use App\Models\Role;

class PostPermissionTest extends BaseRolePermissionTestCase
{
    /**
     * @test
     */
    public function indexCanBeAccessedByUserWithPostBrowsePermission()
    {
        // Arrange
        $role = Role::factory()->create();

        // Act
        $role->givePermissionTo('post.browse');

        $this->user->assignRole($role);

        $response = $this->get(route('admin.posts.index'));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function indexCannotBeAccessedByUserWhoHasNoPostBrowsePermission()
    {
        // Arrange
        $role = Role::factory()->create();

        // Act
        $role->revokePermissionTo('post.browse');

        $this->user->assignRole($role);

        $response = $this->get(route('admin.posts.index'));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function editCanBeAccessedByUserWithPostEditPermission()
    {
        // Arrange
        $role = Role::factory()->create();

        $post = Post::factory()->create();

        // Act
        $role->givePermissionTo('post.edit');

        $this->user->assignRole($role);

        $response = $this->get(route('admin.posts.edit', $post->id));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function editCannotBeAccessedByUserWhoHasNoPostEditPermission()
    {
        // Arrange
        $role = Role::factory()->create();

        $post = Post::factory()->create();

        // Act
        $role->revokePermissionTo('post.edit');

        $this->user->assignRole($role);

        $response = $this->get(route('admin.posts.edit', $post->id));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function updateCanBeAccessedByUserWithPostEditPermission()
    {
        // Arrange
        $role = Role::factory()->create();

        $post = Post::factory()->create();

        // Act
        $role->givePermissionTo('post.edit');

        $this->user->assignRole($role);

        $response = $this->put(route('admin.posts.update', $post->id), [
            'locale' => config('app.fallback_locale'),
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'status' => PublishableInterface::STATUS_DRAFT,
            'content' => '',
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function updateCannotBeAccessedByUserWhoHasNoPostEditPermission()
    {
        // Arrange
        $role = Role::factory()->create();

        $post = Post::factory()->create();

        // Act
        $role->revokePermissionTo('post.edit');

        $this->user->assignRole($role);

        $response = $this->put(route('admin.posts.update', $post->id), [
            'locale' => config('app.fallback_locale'),
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'status' => PublishableInterface::STATUS_DRAFT,
        ]);

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function createCanBeAccessedByUserWithPostAddPermission()
    {
        // Arrange
        $role = Role::factory()->create();

        // Act
        $role->givePermissionTo('post.add');

        $this->user->assignRole($role);

        $response = $this->get(route('admin.posts.create'));

        // Assert
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function createCannotBeAccessedByUserWhoHasNoPostAddPermission()
    {
        // Arrange
        $role = Role::factory()->create();

        // Act
        $role->revokePermissionTo('post.add');

        $this->user->assignRole($role);

        $response = $this->get(route('admin.posts.create'));

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function storeCanBeAccessedByUserWithPostAddPermission()
    {
        // Arrange
        $role = Role::factory()->create();

        // Act
        $role->givePermissionTo('post.add');

        $this->user->assignRole($role);

        $response = $this->post(route('admin.posts.store'), [
            'locale' => config('app.fallback_locale'),
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'status' => PublishableInterface::STATUS_DRAFT,
            'content' => '',
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function storeCannotBeAccessedByUserWhoHasNoPostAddPermission()
    {
        // Arrange
        $role = Role::factory()->create();

        // Act
        $role->revokePermissionTo('post.add');

        $this->user->assignRole($role);

        $response = $this->post(route('admin.posts.store'), [
            'locale' => config('app.fallback_locale'),
            'title' => $this->faker->title,
            'slug' => $this->faker->slug,
            'status' => PublishableInterface::STATUS_DRAFT,
        ]);

        // Assert
        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function deleteCanBeDoneByUserWithPostDeletePermission()
    {
        // Arrange
        $role = Role::factory()->create();

        $role->givePermissionTo('post.delete');

        $post = Post::factory()->create();

        // Act
        $this->user->assignRole($role);

        $response = $this->delete(route('admin.posts.destroy', ['post' => $post->id]));

        // Assert
        $response->assertStatus(302);
    }

    /**
     * @test
     */
    public function deleteCannotBeDoneByUserWhoHasNoDeletePermission()
    {
        // Arrange
        $role = Role::factory()->create();
        $role->revokePermissionTo('post.delete');

        $post = Post::factory()->create();

        // Act
        $this->user->assignRole($role);

        $response = $this->delete(route('admin.posts.destroy', ['post' => $post->id]));

        // Assert
        $response->assertForbidden();
    }
}
