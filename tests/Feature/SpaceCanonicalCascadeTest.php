<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Location;
use App\Models\RoleApplication;
use App\Models\User;
use App\Services\CityService;
use Database\Seeders\GlobalOptionSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceService;
use Tests\TestCase;

class SpaceCanonicalCascadeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(GlobalOptionSeeder::class);
    }

    /** @test */
    public function deleting_a_city_space_cascades_canonical_city_location_and_scopes(): void
    {
        [$city, $citySpace, $locationSpace, $location, $admin] = $this->cityTree();

        $this->assertDatabaseHas('cities', ['id' => $city->id]);
        $this->assertDatabaseHas('locations', ['id' => $location->id]);
        $this->assertTrue($admin->inScope('city', $city->id, config('permission.role_names.city_admin')));

        $citySpace->delete();

        $this->assertDatabaseMissing('cities', ['id' => $city->id]);
        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
        $this->assertDatabaseMissing('spaces', ['id' => $citySpace->id]);
        $this->assertDatabaseMissing('spaces', ['id' => $locationSpace->id]);
        $this->assertDatabaseMissing('user_scope', [
            'user_id' => $admin->id,
            'scope_type' => 'city',
            'scope_id' => $city->id,
        ]);
        $this->assertFalse($admin->fresh()->isCityAdministrator());
    }

    /** @test */
    public function deleting_a_city_space_allows_the_same_city_to_be_recreated(): void
    {
        [$city, $citySpace] = $this->cityTree();
        $name = $city->name;
        $country = $city->country_code;

        $citySpace->delete();

        $recreated = app(CityService::class)->findOrCreate($name, $country);

        $this->assertNotSame($city->id, $recreated->id);
        $this->assertSame($name, $recreated->name);
        $this->assertSame($country, $recreated->country_code);
    }

    /** @test */
    public function deleting_a_location_space_cascades_the_canonical_location_only(): void
    {
        [$city, $citySpace, $locationSpace, $location] = $this->cityTree();

        $locationSpace->delete();

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
        $this->assertDatabaseHas('cities', ['id' => $city->id]);
        $this->assertDatabaseHas('spaces', ['id' => $citySpace->id]);
    }

    /** @test */
    public function deleting_a_city_space_removes_role_applications_that_would_block_city_delete(): void
    {
        [$city, $citySpace] = $this->cityTree();

        RoleApplication::create([
            'email' => 'applicant@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'requested_role' => config('permission.role_names.city_admin'),
            'city_id' => $city->id,
            'status' => 'pending',
        ]);

        $citySpace->delete();

        $this->assertDatabaseMissing('cities', ['id' => $city->id]);
        $this->assertDatabaseMissing('role_applications', ['city_id' => $city->id]);
    }

    /** @test */
    public function persist_city_id_finds_or_creates_a_canonical_city_for_custom_names(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.admin'));
        $this->actingAs($admin);

        $cityTypeId = \App\Models\GlobalOption::where('name', 'City')->value('id');
        $space = Space::create([
            'name' => 'Askersund',
            'type_id' => $cityTypeId,
            'country_code' => 'SE',
            'city' => 'Askersund',
        ]);

        $this->assertNull($space->city_id);

        app(SpaceService::class)->persistCityId($space, [
            'city' => 'Askersund',
            'country_code' => 'SE',
        ]);

        $space->refresh();

        $this->assertNotNull($space->city_id);
        $this->assertDatabaseHas('cities', [
            'id' => $space->city_id,
            'name' => 'Askersund',
            'country_code' => 'SE',
        ]);
    }

    /**
     * @return array{0: City, 1: Space, 2: Space, 3: Location, 4: User}
     */
    private function cityTree(): array
    {
        $actor = User::factory()->create();
        $actor->assignRole(config('permission.role_names.admin'));
        $this->actingAs($actor);

        $cityTypeId = \App\Models\GlobalOption::where('name', 'City')->value('id');
        $pitchTypeId = \App\Models\GlobalOption::where('name', 'Pitch')->value('id');

        $city = City::factory()->create([
            'name' => 'Askersund',
            'country_code' => 'SE',
        ]);

        $citySpace = Space::create([
            'name' => 'Askersund',
            'type_id' => $cityTypeId,
            'city_id' => $city->id,
            'country_code' => 'SE',
        ]);

        $locationSpace = Space::create([
            'name' => 'Harbour Pitch',
            'type_id' => $pitchTypeId,
            'parent_id' => $citySpace->id,
            'city_id' => $city->id,
            'country_code' => 'SE',
        ]);

        $location = Location::factory()->create([
            'city_id' => $city->id,
            'space_id' => $locationSpace->id,
            'name' => 'Harbour Pitch',
        ]);

        $cityAdmin = User::factory()->create();
        $cityAdmin->assignRole(config('permission.role_names.city_admin'));
        $cityAdmin->syncAdminCities([$city->id]);

        return [$city, $citySpace->fresh(), $locationSpace->fresh(), $location, $cityAdmin];
    }
}
