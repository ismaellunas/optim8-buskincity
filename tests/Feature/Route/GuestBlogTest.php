<?php

namespace Tests\Feature\Route;

class GuestBlogTest extends LocalizationRouteTestCase
{
    private $routeName = 'blog.index';

    /**
     * @test
     */
    public function guestCanAccessBlog()
    {
        // Act
        $response = $this->get(
            route($this->routeName)
        );

        // Assert
        $this->assertGuest();
        $response->assertOk();
    }

    /**
     * @test
     */
    public function guestCanRenderTheBlogView()
    {
        // Act
        $response = $this->get(
            route($this->routeName)
        );

        // Assert
        $response->assertViewIs('posts');
    }
}
