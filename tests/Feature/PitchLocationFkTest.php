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
use Modules\Booking\Services\ProductEventService;
use Modules\Booking\Services\ProductSpaceService;
use Modules\Ecommerce\Entities\Product;
use Modules\Space\Entities\Space;
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

    /** @test */
    public function pitch_index_filter_options_are_scoped_for_city_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(config('permission.role_names.city_admin'));

        $assigned = City::factory()->create(['name' => 'Wageningen', 'country_code' => 'NLD']);
        City::factory()->create(['name' => 'Oslo', 'country_code' => 'NOR']);
        City::factory()->create(['name' => 'Amsterdam', 'country_code' => 'NLD']);

        $user->syncAdminCities([$assigned->id]);

        $service = app(ProductEventService::class);

        $cityOptions = $service->getAdminFilterCityOptions($user);
        $countryOptions = $service->getAdminFilterCountryOptions($user);

        $this->assertCount(1, $cityOptions);
        $this->assertSame('Wageningen', $cityOptions[0]['value']);
        $this->assertSame('NLD', $cityOptions[0]['country_code']);

        $this->assertCount(1, $countryOptions);
        $this->assertSame('NLD', $countryOptions[0]['value']);
    }

    /** @test */
    public function scoped_city_admin_requires_saved_location_for_pitch(): void
    {
        $cityAdmin = User::factory()->create();
        $cityAdmin->assignRole(config('permission.role_names.city_admin'));

        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.admin'));

        $scope = app(UserScopeService::class);

        $this->assertTrue($scope->requiresSavedLocationForPitch($cityAdmin));
        $this->assertFalse($scope->requiresSavedLocationForPitch($admin));
    }

    /** @test */
    public function location_payload_from_space_returns_city_name_string(): void
    {
        $city = City::factory()->create(['name' => 'Amsterdam', 'country_code' => 'NLD']);

        $space = Space::create([
            'name' => 'Dam Square',
            'city_id' => $city->id,
            'city' => 'Amsterdam',
            'country_code' => 'NL',
            'address' => 'Dam 1',
            'latitude' => 52.3731,
            'longitude' => 4.8922,
        ]);

        $payload = app(ProductSpaceService::class)->locationPayloadFromSpace($space->id);

        $this->assertNotNull($payload);
        $this->assertIsString($payload['city']);
        $this->assertSame('Amsterdam', $payload['city']);
        $this->assertSame($city->id, $payload['city_id']);
    }

    /** @test */
    public function space_city_name_returns_string_when_city_column_is_empty(): void
    {
        $city = City::factory()->create(['name' => 'Amsterdam', 'country_code' => 'NLD']);

        $space = Space::create([
            'name' => 'Dam Square',
            'city_id' => $city->id,
            'city' => null,
            'country_code' => 'NL',
        ]);

        $this->assertSame('Amsterdam', $space->fresh()->cityName());
    }

    /** @test */
    public function pitch_product_create_casts_attribute_data_to_json(): void
    {
        $this->seed(\Modules\Ecommerce\Database\Seeders\DefaultSeeder::class);

        $productType = \Lunar\Models\ProductType::where('name', 'Event')->first();

        $product = Product::create([
            'product_type_id' => $productType->id,
            'status' => 'draft',
            'attribute_data' => [
                'name' => new \Lunar\FieldTypes\TranslatedText(collect([
                    'en' => new \Lunar\FieldTypes\Text('Dam Square Pitch'),
                ])),
            ],
        ]);

        $this->assertNotNull($product->id);
        $this->assertSame('Dam Square Pitch', $product->translateAttribute('name'));
    }

    /** @test */
    public function empty_space_id_scope_returns_no_spaces(): void
    {
        $service = app(\Modules\Space\Services\SpaceService::class);
        $user = User::factory()->create();

        $records = $service->getRecords($user, [], null);

        $this->assertSame(0, $records->total());
    }
}
