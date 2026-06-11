<?php

namespace Tests\Feature;

use App\Enums\RoleApplicationStatus;
use App\Mail\RoleApplicationApproved;
use App\Models\City;
use App\Models\Media;
use App\Models\RoleApplication;
use App\Models\User;
use App\Models\UserScope;
use App\Models\GlobalOption;
use App\Services\MediaService;
use App\Services\UserService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Modules\Space\Entities\Space;
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

    private function seedNavigableCountries(): void
    {
        $this->seed(\Database\Seeders\CountrySeeder::class);
    }

    private function createCountrySpace(string $name = 'Netherlands', string $countryCode = 'NLD'): Space
    {
        $countryTypeId = GlobalOption::where('name', 'Country')->value('id');

        return Space::create([
            'name' => $name,
            'type_id' => $countryTypeId,
            'country_code' => $countryCode,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function cityAdminApplicationPayload(City $city, Space $countrySpace, array $overrides = []): array
    {
        return array_merge([
            'email' => 'applicant@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'password' => 'SecretPass1',
            'password_confirmation' => 'SecretPass1',
            'requested_role' => config('permission.role_names.city_admin'),
            'country_space_id' => $countrySpace->id,
            'city_id' => $city->id,
        ], $overrides);
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
        $this->seedNavigableCountries();
        $countrySpace = $this->createCountrySpace();
        $city = City::factory()->create(['country_code' => 'NL']);

        $response = $this->post(
            route('role-applications.store'),
            $this->cityAdminApplicationPayload($city, $countrySpace, [
                'description' => 'City branding text',
                'excerpt' => 'Short intro',
            ])
        );

        $response->assertRedirect(route('role-applications.submitted'));

        $application = RoleApplication::where('email', 'applicant@example.com')->first();

        $this->assertNotNull($application);
        $this->assertNotNull($application->password);
        $this->assertTrue(Hash::check('SecretPass1', $application->password));

        $this->assertDatabaseHas('role_applications', [
            'email' => 'applicant@example.com',
            'city_id' => $city->id,
            'country_space_id' => $countrySpace->id,
            'status' => RoleApplicationStatus::PENDING->value,
        ]);
    }

    /** @test */
    public function city_admin_application_requires_country_space(): void
    {
        $city = City::factory()->create();

        $this->post(route('role-applications.store'), [
            'email' => 'applicant@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'password' => 'SecretPass1',
            'password_confirmation' => 'SecretPass1',
            'requested_role' => config('permission.role_names.city_admin'),
            'city_id' => $city->id,
        ])->assertSessionHasErrors('country_space_id');
    }

    /** @test */
    public function city_admin_application_rejects_city_outside_selected_country(): void
    {
        $this->seedNavigableCountries();
        $countrySpace = $this->createCountrySpace('Sweden', 'SWE');
        $city = City::factory()->create(['country_code' => 'NL']);

        $this->post(
            route('role-applications.store'),
            $this->cityAdminApplicationPayload($city, $countrySpace)
        )->assertSessionHasErrors('city_id');
    }

    /** @test */
    public function guest_can_submit_application_with_logo_and_cover(): void
    {
        $this->seedNavigableCountries();
        $countrySpace = $this->createCountrySpace();
        $city = City::factory()->create(['country_code' => 'NL']);
        $logo = Media::factory()->create(['user_id' => null]);
        $cover = Media::factory()->create(['user_id' => null]);

        $this->mock(MediaService::class, function ($mock) use ($logo, $cover) {
            $mock->shouldReceive('sanitizeFileName')->andReturn('branding.jpg');
            $mock->shouldReceive('upload')->twice()->andReturn($logo, $cover);
        });

        $this->post(route('role-applications.store'), array_merge(
            $this->cityAdminApplicationPayload($city, $countrySpace, [
                'email' => 'branded@example.com',
                'first_name' => 'Branded',
                'last_name' => 'Admin',
            ]),
            [
                'logo' => UploadedFile::fake()->image('logo.jpg'),
                'cover' => UploadedFile::fake()->image('cover.jpg'),
            ]
        ))->assertRedirect(route('role-applications.submitted'));

        $application = RoleApplication::where('email', 'branded@example.com')->first();

        $this->assertNotNull($application);
        $this->assertSame($logo->id, $application->logo_media_id);
        $this->assertSame($cover->id, $application->cover_media_id);
    }

    /** @test */
    public function city_admin_application_requires_password(): void
    {
        $this->seedNavigableCountries();
        $countrySpace = $this->createCountrySpace();
        $city = City::factory()->create(['country_code' => 'NL']);

        $this->post(route('role-applications.store'), [
            'email' => 'applicant@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'requested_role' => config('permission.role_names.city_admin'),
            'country_space_id' => $countrySpace->id,
            'city_id' => $city->id,
        ])->assertSessionHasErrors('password');
    }

    /** @test */
    public function city_admin_application_rejects_mismatched_password_confirmation(): void
    {
        $this->seedNavigableCountries();
        $countrySpace = $this->createCountrySpace();
        $city = City::factory()->create(['country_code' => 'NL']);

        $this->post(route('role-applications.store'), [
            'email' => 'applicant@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'password' => 'SecretPass1',
            'password_confirmation' => 'DifferentPass1',
            'requested_role' => config('permission.role_names.city_admin'),
            'country_space_id' => $countrySpace->id,
            'city_id' => $city->id,
        ])->assertSessionHasErrors('password_confirmation');
    }

    /** @test */
    public function special_events_application_does_not_require_password(): void
    {
        $city = City::factory()->create();

        $this->post(route('role-applications.store'), [
            'email' => 'se@example.com',
            'first_name' => 'Special',
            'last_name' => 'Events',
            'requested_role' => config('permission.role_names.special_events_admin'),
            'city_id' => $city->id,
        ])->assertRedirect(route('role-applications.submitted'));

        $this->assertNull(RoleApplication::where('email', 'se@example.com')->value('password'));
    }

    /** @test */
    public function logged_in_user_can_submit_application_with_a_different_email(): void
    {
        $this->seedNavigableCountries();
        $countrySpace = $this->createCountrySpace();
        $city = City::factory()->create(['country_code' => 'NL']);
        $user = User::factory()->create(['email' => 'existing@example.com']);

        $this->actingAs($user)->post(
            route('role-applications.store'),
            $this->cityAdminApplicationPayload($city, $countrySpace, [
                'email' => 'newadmin@example.com',
                'first_name' => 'New',
                'last_name' => 'Admin',
            ])
        )->assertRedirect(route('role-applications.submitted'));

        $this->assertDatabaseHas('role_applications', [
            'email' => 'newadmin@example.com',
            'user_id' => null,
            'status' => RoleApplicationStatus::PENDING->value,
        ]);
    }

    /** @test */
    public function logged_in_user_submission_links_user_when_email_matches(): void
    {
        $this->seedNavigableCountries();
        $countrySpace = $this->createCountrySpace();
        $city = City::factory()->create(['country_code' => 'NL']);
        $user = User::factory()->create(['email' => 'same@example.com']);

        $this->actingAs($user)->post(
            route('role-applications.store'),
            $this->cityAdminApplicationPayload($city, $countrySpace, [
                'email' => 'same@example.com',
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ])
        )->assertRedirect(route('role-applications.submitted'));

        $this->assertDatabaseHas('role_applications', [
            'email' => 'same@example.com',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function administrator_can_approve_and_provision_city_admin(): void
    {
        Mail::fake();

        $this->seedNavigableCountries();
        $countrySpace = $this->createCountrySpace();
        $city = City::factory()->create(['country_code' => 'NL']);
        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.admin'));

        $application = RoleApplication::create([
            'email' => 'newadmin@example.com',
            'first_name' => 'New',
            'last_name' => 'Admin',
            'password' => UserService::hashPassword('SecretPass1'),
            'requested_role' => config('permission.role_names.city_admin'),
            'city_id' => $city->id,
            'country_space_id' => $countrySpace->id,
            'status' => RoleApplicationStatus::PENDING,
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.role-applications.approve', $application),
            ['confirm_replace' => false]
        );

        $response->assertRedirect(route('admin.role-applications.show', $application));

        $application->refresh();
        $this->assertSame(RoleApplicationStatus::APPROVED, $application->status);
        $this->assertNull($application->password);

        $user = User::where('email', 'newadmin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isCityAdministrator());
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->isCityAdmin($city->id));
        $this->assertTrue(Hash::check('SecretPass1', $user->password));

        Mail::assertSent(RoleApplicationApproved::class, function (RoleApplicationApproved $mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $cityTypeId = GlobalOption::where('name', 'City')->value('id');
        $citySpace = Space::where('type_id', $cityTypeId)
            ->where('city_id', $city->id)
            ->first();

        $this->assertNotNull($citySpace);
        $this->assertSame($countrySpace->id, $citySpace->parent_id);
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
