<?php

namespace Tests\Feature;

use App\Enums\RoleApplicationStatus;
use App\Models\City;
use App\Models\RoleApplication;
use App\Models\User;
use App\Models\UserScope;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoleApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // PermissionSeeder must run first so RolesAndPermissionsSeeder can grant
        // system.* permissions (incl. system.dashboard) required by admin routes.
        $this->seed(PermissionSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\GlobalOptionSeeder::class);
        Storage::fake('public');
        $this->withoutMiddleware(\App\Http\Middleware\Recaptcha::class);
    }

    /** @test */
    public function guest_can_view_the_apply_page(): void
    {
        $this->get(route('role-applications.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('RoleApplication/Apply')
                ->missing('cityOptions')
                ->where('requestedRole', config('permission.role_names.city_admin'))
            );
    }

    /** @test */
    public function guest_can_search_cities_for_application(): void
    {
        City::factory()->create(['name' => 'Wageningen', 'country_code' => 'NLD']);
        City::factory()->create(['name' => 'Amsterdam', 'country_code' => 'NLD']);

        $this->getJson(route('cities.search', ['search' => 'Wage']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Wageningen']);

        $this->getJson(route('cities.search', ['search' => 'A']))
            ->assertOk()
            ->assertJsonCount(0);
    }

    /** @test */
    public function guest_can_search_swedish_cities_by_name_country_or_ascii_variant(): void
    {
        $this->seed(\Database\Seeders\CountrySeeder::class);

        City::factory()->create(['name' => 'Göteborg', 'country_code' => 'SE']);
        City::factory()->create(['name' => 'Stockholm', 'country_code' => 'SE']);

        $this->getJson(route('cities.search', ['search' => 'Stockholm']))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Stockholm', 'country_code' => 'SE']);

        $this->getJson(route('cities.search', ['search' => 'Goteborg']))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Göteborg', 'country_code' => 'SE']);

        $this->getJson(route('cities.search', ['search' => 'Sweden']))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Stockholm', 'country_code' => 'SE']);
    }

    /** @test */
    public function guest_can_submit_a_city_admin_application(): void
    {
        $city = City::factory()->create();

        $response = $this->post(route('role-applications.store'), [
            'email' => 'applicant@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'requested_role' => config('permission.role_names.city_admin'),
            'city_id' => $city->id,
            'description' => 'City branding text',
            'excerpt' => 'Short intro',
        ]);

        $response->assertRedirect(route('role-applications.submitted'));

        $this->assertDatabaseHas('role_applications', [
            'email' => 'applicant@example.com',
            'city_id' => $city->id,
            'status' => RoleApplicationStatus::PENDING->value,
        ]);
    }

    /** @test */
    public function administrator_can_approve_and_provision_city_admin(): void
    {
        $city = City::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.admin'));

        $application = RoleApplication::create([
            'email' => 'newadmin@example.com',
            'first_name' => 'New',
            'last_name' => 'Admin',
            'requested_role' => config('permission.role_names.city_admin'),
            'city_id' => $city->id,
            'status' => RoleApplicationStatus::PENDING,
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.role-applications.approve', $application),
            ['confirm_replace' => false]
        );

        $response->assertRedirect(route('admin.role-applications.show', $application));

        $application->refresh();
        $this->assertSame(RoleApplicationStatus::APPROVED, $application->status);

        $user = User::where('email', 'newadmin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isCityAdministrator());
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->isCityAdmin($city->id));
    }

    /** @test */
    public function approving_city_admin_application_replaces_existing_admin_when_confirmed(): void
    {
        $city = City::factory()->create();

        $existing = User::factory()->create();
        $existing->assignRole(config('permission.role_names.city_admin'));
        $existing->syncAdminCities([$city->id]);

        $reviewer = User::factory()->create();
        $reviewer->assignRole(config('permission.role_names.admin'));

        $application = RoleApplication::create([
            'email' => 'replacement@example.com',
            'first_name' => 'Replace',
            'last_name' => 'Admin',
            'requested_role' => config('permission.role_names.city_admin'),
            'city_id' => $city->id,
            'status' => RoleApplicationStatus::PENDING,
        ]);

        $this->actingAs($reviewer)->post(
            route('admin.role-applications.approve', $application),
            ['confirm_replace' => true]
        )->assertRedirect();

        $application->refresh();
        $this->assertSame($existing->id, $application->replaced_user_id);

        $existing->refresh();
        $this->assertFalse($existing->isCityAdmin($city->id));

        $newAdmin = User::where('email', 'replacement@example.com')->first();
        $this->assertTrue($newAdmin->isCityAdmin($city->id));
    }

    /** @test */
    public function protected_admin_email_is_rejected_on_approval(): void
    {
        $city = City::factory()->create();
        $super = User::factory()->create(['email' => 'protected@example.com']);
        $super->assignRole(config('permission.super_admin_role'));

        $reviewer = User::factory()->create();
        $reviewer->assignRole(config('permission.role_names.admin'));

        $application = RoleApplication::create([
            'email' => 'protected@example.com',
            'first_name' => 'Bad',
            'last_name' => 'Actor',
            'requested_role' => config('permission.role_names.city_admin'),
            'city_id' => $city->id,
            'status' => RoleApplicationStatus::PENDING,
        ]);

        $this->actingAs($reviewer)->post(
            route('admin.role-applications.approve', $application),
            ['confirm_replace' => false]
        )->assertSessionHasErrors();

        $this->assertSame(RoleApplicationStatus::PENDING, $application->fresh()->status);
    }

    /** @test */
    public function many_special_events_admins_are_allowed_for_one_city(): void
    {
        $city = City::factory()->create();
        $reviewer = User::factory()->create();
        $reviewer->assignRole(config('permission.role_names.admin'));

        foreach (['se1@example.com', 'se2@example.com'] as $email) {
            $application = RoleApplication::create([
                'email' => $email,
                'first_name' => 'SE',
                'last_name' => 'Admin',
                'requested_role' => config('permission.role_names.special_events_admin'),
                'city_id' => $city->id,
                'status' => RoleApplicationStatus::PENDING,
            ]);

            $this->actingAs($reviewer)->post(
                route('admin.role-applications.approve', $application)
            )->assertRedirect();
        }

        $this->assertSame(2, UserScope::query()
            ->where('role', config('permission.role_names.special_events_admin'))
            ->where('scope_type', 'city')
            ->where('scope_id', $city->id)
            ->count());
    }
}
