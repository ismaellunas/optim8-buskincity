<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\GlobalOption;
use App\Models\User;
use Database\Seeders\CountryTestSeeder;
use Database\Seeders\GlobalOptionSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Space\Database\Seeders\PermissionSeeder as SpacePermissionSeeder;
use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceHierarchyService;
use Tests\TestCase;

class SpaceHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(GlobalOptionSeeder::class);
        $this->seed(CountryTestSeeder::class);
        $this->seed(SpacePermissionSeeder::class);

        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureLoginFromAdminLoginRoute::class,
            \App\Http\Middleware\UserEmailIsVerified::class,
            \App\Http\Middleware\VerifyModule::class,
        ]);
    }

    /** @test */
    public function city_admin_pitch_is_inferred_when_created_under_a_city(): void
    {
        [$country, $citySpace] = $this->countryAndCitySpaces();
        $admin = $this->cityAdminFor($citySpace);

        $response = $this->actingAs($admin)->post(route('admin.spaces.store'), [
            'name' => 'Dam Square Pitch',
            'parent_id' => $citySpace->id,
            'country_code' => 'NL',
            'city_id' => $citySpace->city_id,
        ]);

        $response->assertRedirect();

        $pitch = Space::where('name', 'Dam Square Pitch')->first();

        $this->assertNotNull($pitch);
        $this->assertSame(
            GlobalOption::where('name', 'Pitch')->value('id'),
            $pitch->type_id
        );
    }

    /** @test */
    public function city_admin_cannot_create_a_location_under_a_country(): void
    {
        [$country] = $this->countryAndCitySpaces();
        $city = City::factory()->create();
        $admin = $this->cityAdminFor(
            Space::where('type_id', GlobalOption::where('name', 'City')->value('id'))->first()
        );

        $response = $this->actingAs($admin)->post(route('admin.spaces.store'), [
            'name' => 'Invalid Pitch',
            'parent_id' => $country->id,
            'country_code' => 'NL',
            'city_id' => $city->id,
        ]);

        $response->assertSessionHasErrors('parent_id');
    }

    /** @test */
    public function global_admin_infers_pitch_type_when_parent_is_a_city(): void
    {
        [, $citySpace] = $this->countryAndCitySpaces();
        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.admin'));

        $typeId = app(SpaceHierarchyService::class)->inferTypeIdForCreate($citySpace, $admin);

        $this->assertSame(
            GlobalOption::where('name', 'Pitch')->value('id'),
            $typeId
        );
    }

    /** @test */
    public function hierarchy_service_rejects_city_under_city(): void
    {
        [, $citySpace] = $this->countryAndCitySpaces();
        $cityTypeId = GlobalOption::where('name', 'City')->value('id');

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(SpaceHierarchyService::class)->assertValidHierarchy(
            $citySpace,
            (int) $cityTypeId,
            User::factory()->create()
        );
    }

    /**
     * @return array{0: Space, 1: Space}
     */
    private function countryAndCitySpaces(): array
    {
        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.admin'));

        $this->actingAs($admin);

        $countryTypeId = GlobalOption::where('name', 'Country')->value('id');
        $cityTypeId = GlobalOption::where('name', 'City')->value('id');
        $city = City::factory()->create(['name' => 'Amsterdam', 'country_code' => 'NLD']);

        $country = Space::create([
            'name' => 'Netherlands',
            'type_id' => $countryTypeId,
            'country_code' => 'NL',
        ]);

        $citySpace = Space::create([
            'name' => 'Amsterdam',
            'type_id' => $cityTypeId,
            'parent_id' => $country->id,
            'city_id' => $city->id,
            'country_code' => 'NL',
        ]);

        return [$country, $citySpace];
    }

    private function cityAdminFor(Space $citySpace): User
    {
        $user = User::factory()->create();
        $user->assignRole(config('permission.role_names.city_admin'));
        $user->syncAdminCities([(int) $citySpace->city_id]);

        return $user;
    }
}
