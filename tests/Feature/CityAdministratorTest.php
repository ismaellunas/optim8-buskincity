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
        
        // Seed roles and permissions
        $this->seed(\Database\Seeders\CityAdministratorSeeder::class);
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
        $city = City::factory()->create();

        $user->adminCities()->attach($city);

        $this->assertTrue($user->isCityAdmin($city->id));
        $this->assertTrue($user->adminCities->contains($city));
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
