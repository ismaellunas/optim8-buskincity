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
    public function city_admin_location_city_is_forced_from_parent(): void
    {
        [$country, $citySpace] = $this->countryAndCitySpaces();
        $otherCity = City::factory()->create(['country_code' => 'SE']);
        $admin = $this->cityAdminFor($citySpace);

        $response = $this->actingAs($admin)->post(route('admin.spaces.store'), [
            'name' => 'Scoped Location',
            'parent_id' => $citySpace->id,
            'country_code' => 'SE',
            'city_id' => $otherCity->id,
            'city' => 'Stockholm',
        ]);

        $response->assertRedirect();

        $location = Space::where('name', 'Scoped Location')->first();

        $this->assertNotNull($location);
        $this->assertSame((int) $citySpace->city_id, (int) $location->city_id);
    }

    /** @test */
    public function space_options_for_city_admin_mark_city_nodes_as_disabled(): void
    {
        [$country, $citySpace] = $this->countryAndCitySpaces();
        $admin = $this->cityAdminFor($citySpace);

        $this->actingAs($admin)->post(route('admin.spaces.store'), [
            'name' => 'Dam Square',
            'parent_id' => $citySpace->id,
            'country_code' => 'NL',
            'city_id' => $citySpace->city_id,
        ]);

        $location = Space::where('name', 'Dam Square')->first();

        $this->assertNotNull($location);

        $options = app(\Modules\Booking\Services\ProductSpaceService::class)->getSpaceOptions();

        $locationOption = $options->firstWhere('id', $location->id);
        $cityOption = $options->firstWhere('id', $citySpace->id);

        $this->assertFalse($locationOption['is_disabled']);
        $this->assertTrue($cityOption['is_disabled']);
        $this->assertSame('Amsterdam', $locationOption['location']['city']);
    }

    /** @test */
    public function city_admin_with_user_scope_only_sees_created_locations_in_pitch_picker(): void
    {
        [$country, $citySpace] = $this->countryAndCitySpaces();

        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.city_admin'));
        $admin->syncScopeCities(
            config('permission.role_names.city_admin'),
            [(int) $citySpace->city_id]
        );

        $this->actingAs($admin)->post(route('admin.spaces.store'), [
            'name' => 'Museumplein',
            'parent_id' => $citySpace->id,
            'country_code' => 'NL',
            'city_id' => $citySpace->city_id,
        ]);

        $location = Space::where('name', 'Museumplein')->first();
        $this->assertNotNull($location);

        $options = app(\Modules\Booking\Services\ProductSpaceService::class)->getSpaceOptions();
        $locationOption = $options->firstWhere('id', $location->id);

        $this->assertNotNull($locationOption);
        $this->assertFalse($locationOption['is_disabled']);
    }

    /** @test */
    public function special_events_admin_sees_city_admin_locations_in_pitch_picker(): void
    {
        [$country, $citySpace] = $this->countryAndCitySpaces();
        $cityAdmin = $this->cityAdminFor($citySpace);

        $this->actingAs($cityAdmin)->post(route('admin.spaces.store'), [
            'name' => 'City Hall Square',
            'parent_id' => $citySpace->id,
            'country_code' => 'NL',
            'city_id' => $citySpace->city_id,
        ]);

        $location = Space::where('name', 'City Hall Square')->first();
        $this->assertNotNull($location);

        $seAdmin = User::factory()->create();
        $seAdmin->assignRole(config('permission.role_names.special_events_admin'));
        $seAdmin->syncScopeCities(
            config('permission.role_names.special_events_admin'),
            [(int) $citySpace->city_id]
        );

        $this->actingAs($seAdmin);

        $options = app(\Modules\Booking\Services\ProductSpaceService::class)->getSpaceOptions();
        $locationOption = $options->firstWhere('id', $location->id);

        $this->assertNotNull($locationOption);
        $this->assertFalse($locationOption['is_disabled']);
    }

    /** @test */
    public function city_admin_can_update_a_location_without_resubmitting_parent(): void
    {
        [$country, $citySpace] = $this->countryAndCitySpaces();
        $admin = $this->cityAdminFor($citySpace);

        $this->actingAs($admin)->post(route('admin.spaces.store'), [
            'name' => 'Dam Square',
            'parent_id' => $citySpace->id,
            'country_code' => 'NL',
            'city_id' => $citySpace->city_id,
            'address' => 'Dam 1',
            'latitude' => 52.3731,
            'longitude' => 4.8922,
        ])->assertRedirect();

        $location = Space::where('name', 'Dam Square')->first();

        $this->assertNotNull($location);

        $this->assertTrue(
            $admin->fresh()->spaces->contains('id', $location->id),
            'City admin should be linked to the location they created'
        );

        $response = $this->actingAs($admin->fresh())->put(route('admin.spaces.update', $location->id), [
            'name' => 'Dam Square Updated',
            'address' => 'Dam 2',
            'country_code' => 'NL',
            'city_id' => $citySpace->city_id,
            'latitude' => 52.3732,
            'longitude' => 4.8923,
        ]);

        $response->assertRedirect()->assertSessionHasNoErrors();

        $location->refresh();

        $this->assertSame('Dam Square Updated', $location->name);
        $this->assertSame('Dam 2', $location->address);
        $this->assertSame((int) $citySpace->id, (int) $location->parent_id);
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
    public function admin_can_view_a_city_page_that_has_no_bookable_product(): void
    {
        [$country, $citySpace] = $this->countryAndCitySpaces();
        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.admin'));

        $citySpace->load('page.translations');
        $slugs = $citySpace->page->translate(defaultLocale())->getSlugs();

        $this->actingAs($admin)
            ->get(route('frontend.spaces.show', ['slugs' => $slugs]))
            ->assertOk();
    }

    /** @test */
    public function creating_a_second_space_with_the_same_name_gets_a_unique_page_slug(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.admin'));
        $this->actingAs($admin);

        $cityTypeId = GlobalOption::where('name', 'City')->value('id');

        Space::create([
            'name' => 'Borås',
            'type_id' => $cityTypeId,
            'country_code' => 'SE',
        ]);

        Space::create([
            'name' => 'Borås',
            'type_id' => $cityTypeId,
            'country_code' => 'SE',
        ]);

        $slugs = Space::where('name', 'Borås')
            ->with('page.translations')
            ->get()
            ->map(fn (Space $space) => $space->page->translate(defaultLocale())->slug)
            ->all();

        $this->assertCount(2, $slugs);
        $this->assertCount(2, array_unique($slugs));
        $this->assertTrue(collect($slugs)->contains('boras'));
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
