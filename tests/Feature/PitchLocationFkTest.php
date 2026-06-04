<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Location;
use App\Models\User;
use App\Services\LocationService;
use App\Services\UserScopeService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PitchLocationFkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /** @test */
    public function location_service_creates_a_canonical_location_for_a_city(): void
    {
        $city = City::factory()->create(['name' => 'Amsterdam', 'country_code' => 'NLD']);

        $location = app(LocationService::class)->findOrCreateFromPitchData($city, [
            'address' => 'Dam Square 1',
            'city' => 'Amsterdam',
            'country_code' => 'NLD',
            'latitude' => 52.3731,
            'longitude' => 4.8922,
        ]);

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'city_id' => $city->id,
            'address' => 'Dam Square 1',
        ]);
    }

    /** @test */
    public function scoped_city_ids_include_city_admin_scope(): void
    {
        $user = User::factory()->create();
        $user->assignRole(config('permission.role_names.city_admin'));

        $city = City::factory()->create();
        $user->syncAdminCities([$city->id]);

        $scoped = app(UserScopeService::class)->scopedCityIds($user);

        $this->assertContains($city->id, $scoped);
    }

    /** @test */
    public function globally_scoped_admin_has_no_city_restriction(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.admin'));

        $this->assertTrue(app(UserScopeService::class)->isGloballyScoped($admin));
        $this->assertTrue(app(UserScopeService::class)->cityIdIsInScope(99999, $admin));
    }

    /** @test */
    public function products_table_has_location_fk_columns(): void
    {
        $table = config('lunar.database.table_prefix').'products';

        $this->assertTrue(Schema::hasColumn($table, 'city_id'));
        $this->assertTrue(Schema::hasColumn($table, 'location_id'));
        $this->assertTrue(Schema::hasColumn($table, 'is_special_event'));
    }
}
