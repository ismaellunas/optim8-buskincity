<?php

namespace Tests\Feature\Route;

class GuestBlogCategoryTest extends LocalizationRouteTestCase
{
    private $routeName = 'blog.category.index';

    /**
     * @test
     */
    public function guestCanAccessBlogCategory()
    {
        // Arrange
        $category = $this->createCategory();

        // Act
        $response = $this->get(
            route($this->routeName, $category->id)
        );

        // Assert
        $this->assertGuest();
        $response->assertOk();
    }

    /**
     * @test
     */
    public function guestCanRenderTheBlogCategoryView()
    {
        // Arrange
        $category = $this->createCategory();

        // Act
        $response = $this->get(
            route($this->routeName, $category->id)
        );

        // Assert
        $response->assertViewIs('posts');
    }

    /**
     * @test
     */
    public function guestCanSeeTheBlogLists()
    {
        // Arrange
        $post = $this->createPublishedPost();

        // Act
        $response = $this->get(
            route($this->routeName, $post->categories[0]->id)
        );

        // Assert
        $response->assertOk()
            ->assertSee($post->title);
    }
}
