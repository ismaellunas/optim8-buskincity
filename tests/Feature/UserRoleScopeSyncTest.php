<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Language;
use App\Models\Role;
use App\Models\User;
use App\Models\UserScope;
use App\Services\UserRoleService;
use Database\Seeders\LanguageTestSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleScopeSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(LanguageTestSeeder::class);
    }

    /** @test */
    public function demoting_city_admin_to_performer_clears_city_scopes(): void
    {
        $user = User::factory()->create();
        $user->assignRole(config('permission.role_names.city_admin'));
        $city = City::factory()->create();
        $user->syncAdminCities([$city->id]);

        $this->assertTrue($user->inScope('city', $city->id, config('permission.role_names.city_admin')));
        $this->assertTrue($user->adminCities->contains($city));

        app(UserRoleService::class)->syncSingleRole(
            $user,
            config('permission.role_names.performer')
        );

        $user = $user->fresh();

        $this->assertTrue($user->hasRole(config('permission.role_names.performer')));
        $this->assertFalse($user->isCityAdministrator());
        $this->assertCount(0, $user->adminCities);
        $this->assertFalse($user->inScope('city', $city->id, config('permission.role_names.city_admin')));
        $this->assertDatabaseMissing('user_scope', [
            'user_id' => $user->id,
            'role' => config('permission.role_names.city_admin'),
            'scope_id' => $city->id,
        ]);
    }

    /** @test */
    public function changing_city_admin_to_special_events_admin_clears_city_admin_scopes(): void
    {
        $user = User::factory()->create();
        $user->assignRole(config('permission.role_names.city_admin'));
        $city = City::factory()->create();
        $user->syncAdminCities([$city->id]);

        app(UserRoleService::class)->syncSingleRole(
            $user,
            config('permission.role_names.special_events_admin')
        );

        $user = $user->fresh();

        $this->assertTrue($user->isSpecialEventsAdmin());
        $this->assertFalse($user->isCityAdministrator());
        $this->assertCount(0, $user->adminCities);
        $this->assertFalse($user->inScope('city', $city->id, config('permission.role_names.city_admin')));
        $this->assertCount(
            0,
            $user->scopeIdsFor(config('permission.role_names.special_events_admin'), 'city')
        );
    }

    /** @test */
    public function user_update_assigns_cities_in_the_same_write_as_the_role(): void
    {
        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureLoginFromAdminLoginRoute::class,
            \App\Http\Middleware\UserEmailIsVerified::class,
        ]);

        $admin = User::factory()->create();
        $admin->assignRole(config('permission.super_admin_role'));
        $this->actingAs($admin);

        $user = User::factory()->create([
            'language_id' => Language::code(config('app.locale'))->value('id'),
        ]);
        $city = City::factory()->create();
        $cityAdminRole = Role::findByName(config('permission.role_names.city_admin'));

        $this->put(route('admin.users.update', $user), [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'language_id' => $user->language_id,
            'role' => $cityAdminRole->id,
            'cities' => [$city->id],
        ])->assertSessionHasNoErrors();

        $user = $user->fresh();

        $this->assertTrue($user->isCityAdministrator());
        $this->assertTrue($user->adminCities->contains($city));
        $this->assertTrue($user->inScope('city', $city->id, config('permission.role_names.city_admin')));
    }

    /** @test */
    public function leftover_special_events_scopes_are_cleared_when_role_is_unchanged_city_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(config('permission.role_names.city_admin'));
        $city = City::factory()->create();
        $user->syncAdminCities([$city->id]);

        UserScope::create([
            'user_id' => $user->id,
            'role' => config('permission.role_names.special_events_admin'),
            'scope_type' => 'city',
            'scope_id' => $city->id,
        ]);

        app(UserRoleService::class)->syncSingleRole(
            $user,
            config('permission.role_names.city_admin')
        );

        $this->assertTrue($user->fresh()->inScope('city', $city->id, config('permission.role_names.city_admin')));
        $this->assertFalse($user->fresh()->inScope(
            'city',
            $city->id,
            config('permission.role_names.special_events_admin')
        ));
    }
}
