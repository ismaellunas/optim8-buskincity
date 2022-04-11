<?php

namespace Tests\Feature\Route;

use App\Models\{
    Role,
    User,
};

class GuestPublicPageTest extends BaseRouteTestCase
{
    private $basePermissionName = 'public_page';
    private $role;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Arrange
        $this->user = User::factory()->create();
        $this->role = Role::factory()->create([
                'name' => 'performer',
            ]);

        $this->user->assignRole($this->role);
    }

    /**
     * @test
     */
    public function guestCanAccessPublicPageWithPerformanceHaveAPermission()
    {
        // Arrange
        $this->givePermissionToRole('profile');

        // Act
        $response = $this->get(
            $this->user->profilePageUrl
        );

        // Assert
        $this->assertGuest();
        $response->assertOk();
    }

    /**
     * @test
     */
    public function guestCannotAccessPublicPageWithPerformanceNotHaveAPermission()
    {
        // Act
        $response = $this->get(
            $this->user->profilePageUrl
        );

        // Assert
        $this->assertGuest();
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function guestCanRenderPublicPageView()
    {
        // Arrange
        $this->givePermissionToRole('profile');

        // Act
        $response = $this->get(
            $this->user->profilePageUrl
        );

        // Assert
        $response->assertViewIs('profile-performer');
    }

    private function givePermissionToRole(string $permission, string $basePermission = null)
    {
        $this->role->givePermissionTo(
            $basePermission ?? $this->basePermissionName.
            '.'.
            $permission
        );
    }
}
