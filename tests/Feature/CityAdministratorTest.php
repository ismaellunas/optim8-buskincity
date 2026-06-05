<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\Space\Entities\Space;
use Modules\Space\Entities\SpaceEvent;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CityAdministratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function city_administrator_receives_client_auth_token_on_spaces_index(): void
    {
        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureLoginFromAdminLoginRoute::class,
            \App\Http\Middleware\VerifyModule::class,
        ]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('city_administrator');

        $response = $this->actingAs($user)->get(route('admin.spaces.index'));

        $response->assertSuccessful();
        $response->assertCookie('buskincity_auth_client');
        $response->assertCookie('buskincity_auth_client_expiry');
    }

    /** @test */
    public function city_administrator_can_log_in_via_admin_login_without_dashboard_permission(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret-password'),
        ]);
        $user->assignRole('city_administrator');

        $this->assertFalse($user->can('system.dashboard'));

        $response = $this->post(route('admin.login.attempt'), [
            'email' => $user->email,
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('admin.spaces.index'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_can_assign_city_administrator_role_to_user()
    {
        $user = User::factory()->create();
        $user->assignRole('city_administrator');

        $this->assertTrue($user->hasRole('city_administrator'));
        $this->assertTrue($user->can('city.manage_events'));
    }

    /** @test */
    public function it_can_assign_cities_to_administrator()
    {
        $user = User::factory()->create();
        $user->assignRole(config('permission.role_names.city_admin'));

        $city = City::factory()->create();
        $user->syncAdminCities([$city->id]);

        $this->assertTrue($user->isCityAdmin($city->id));
        $this->assertTrue($user->adminCities->contains($city));
        $this->assertTrue($user->inScope('city', $city->id, config('permission.role_names.city_admin')));
    }

    /** @test */
    public function city_admin_can_view_events_in_their_city()
    {
        $user = User::factory()->create();
        $user->assignRole('city_administrator');
        
        $city = City::factory()->create();
        $user->adminCities()->attach($city);

        $event = SpaceEvent::factory()->create([
            'city_id' => $city->id
        ]);

        $this->assertTrue($user->can('view', $event));
    }

    /** @test */
    public function city_admin_cannot_view_events_in_other_cities()
    {
        $user = User::factory()->create();
        $user->assignRole('city_administrator');
        
        $myCity = City::factory()->create();
        $otherCity = City::factory()->create();
        
        $user->adminCities()->attach($myCity);

        $event = SpaceEvent::factory()->create([
            'city_id' => $otherCity->id
        ]);

        $this->assertFalse($user->can('view', $event));
    }

    /** @test */
    public function city_admin_can_update_events_in_their_city()
    {
        $user = User::factory()->create();
        $user->assignRole('city_administrator');
        
        $city = City::factory()->create();
        $user->adminCities()->attach($city);

        $event = SpaceEvent::factory()->create([
            'city_id' => $city->id
        ]);

        $this->assertTrue($user->can('update', $event));
    }

    /** @test */
    public function city_admin_cannot_update_events_in_other_cities()
    {
        $user = User::factory()->create();
        $user->assignRole('city_administrator');
        
        $myCity = City::factory()->create();
        $otherCity = City::factory()->create();
        
        $user->adminCities()->attach($myCity);

        $event = SpaceEvent::factory()->create([
            'city_id' => $otherCity->id
        ]);

        $this->assertFalse($user->can('update', $event));
    }

}
