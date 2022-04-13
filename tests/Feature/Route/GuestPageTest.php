<?php

namespace Tests\Feature\Route;

class GuestPageTest extends LocalizationRouteTestCase
{
    private $routeName = 'frontend.pages.show';

    /**
     * @test
     */
    public function guestCanAccessPageWithPublishedStatus()
    {
        // Arrange
        $page = $this->getPublishedPage();

        // Act
        $response = $this->get(
            route($this->routeName, $page->slug)
        );

        // Assert
        $this->assertGuest();
        $response->assertOk();
    }

    /**
     * @test
     */
    public function guestCannotAccessPageWithDraftStatus()
    {
        // Arrange
        $page = $this->getDraftPage();

        // Act
        $response = $this->get(
            route($this->routeName, $page->slug)
        );

        // Assert
        $this->assertGuest();
        $response
            ->assertStatus(302)
            ->assertRedirect(
                route('homepage')
            );
    }

    /**
     * @test
     */
    public function guestCanRenderThePage()
    {
        // Arrange
        $page = $this->getPublishedPage();

        // Act
        $response = $this->get(
            route($this->routeName, $page->slug)
        );

        // Assert
        $response->assertViewIs('page');
    }
}
