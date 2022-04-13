<?php

namespace Tests\Feature\Route;

class GuestBlogPostTest extends LocalizationRouteTestCase
{
    private $routeName = 'blog.show';

    /**
     * @test
     */
    public function guestCanAccessBlogPostWithPublishedStatus()
    {
        // Arrange
        $post = $this->createPublishedPost();

        // Act
        $response = $this->get(
            route($this->routeName, $post->slug)
        );

        // Assert
        $this->assertGuest();
        $response
            ->assertOk()
            ->assertSee($post->title);
    }

    /**
     * @test
     */
    public function guestCannotAccessBlogPostWithScheduledStatus()
    {
        // Arrange
        $post = $this->createScheduledPost();

        // Act
        $response = $this->get(
            route($this->routeName, $post->slug)
        );

        // Assert
        $this->assertGuest();
        $response
            ->assertStatus(302)
            ->assertRedirect(
                route('blog.index')
            );
    }

    /**
     * @test
     */
    public function guestCannotAccessBlogPostWithDraftStatus()
    {
        // Arrange
        $post = $this->createDraftPost();

        // Act
        $response = $this->get(
            route($this->routeName, $post->slug)
        );

        // Assert
        $this->assertGuest();
        $response
            ->assertStatus(302)
            ->assertRedirect(
                route('blog.index')
            );
    }

    /**
     * @test
     */
    public function guestCanRenderTheBlogPostView()
    {
        // Arrange
        $post = $this->createPublishedPost();

        // Act
        $response = $this->get(
            route($this->routeName, $post->slug)
        );

        // Assert
        $response->assertViewIs('post');
    }
}
