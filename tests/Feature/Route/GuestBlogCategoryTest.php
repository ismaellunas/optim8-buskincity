<?php

namespace Tests\Feature\Route;

class GuestBlogCategoryTest extends BaseRouteTestCase
{
    private $routeName = 'blog.category.index';

    /**
     * @test
     */
    public function guestCanAccessBlogCategory()
    {
        // Arrange
        $category = $this->getCategory();

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
        $category = $this->getCategory();

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
        $post = $this->getPublishedPost();

        // Act
        $response = $this->get(
            route($this->routeName, $post->categories[0]->id)
        );

        // Assert
        $response->assertOk()
            ->assertSee($post->title);
    }
}
