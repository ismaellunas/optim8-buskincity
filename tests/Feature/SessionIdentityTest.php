<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LoginService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionIdentityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function city_administrator_frontend_login_uses_admin_identity(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret-password'),
        ]);
        $user->assignRole(config('permission.role_names.city_admin'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('admin.spaces.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(LoginService::isAdminHomeUrl());
    }

    /** @test */
    public function city_administrator_can_open_admin_spaces_after_frontend_login(): void
    {
        $this->withoutMiddleware([
            \App\Http\Middleware\VerifyModule::class,
            \App\Http\Middleware\UserEmailIsVerified::class,
        ]);

        $user = User::factory()->create([
            'password' => bcrypt('secret-password'),
        ]);
        $user->assignRole(config('permission.role_names.city_admin'));

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('admin.spaces.index'));

        $this->get(route('admin.spaces.index'))->assertOk();
    }

    /** @test */
    public function performer_frontend_login_stays_on_the_user_portal(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret-password'),
        ]);
        $user->assignRole(config('permission.role_names.performer'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(\App\Providers\RouteServiceProvider::HOME);
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(LoginService::isUserHomeUrl());
    }

    /** @test */
    public function special_events_admin_frontend_login_uses_admin_identity(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret-password'),
        ]);
        $user->assignRole(config('permission.role_names.special_events_admin'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('admin.booking.products.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(LoginService::isAdminHomeUrl());
    }
}
