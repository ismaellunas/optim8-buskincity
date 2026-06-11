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
use Modules\Booking\Services\PitchBookingService;
use Modules\Booking\Services\ProductSpaceService;
use Modules\Ecommerce\Database\Seeders\DefaultSeeder as EcommerceDefaultSeeder;
use Modules\Ecommerce\Entities\Product;
use Modules\Space\Database\Seeders\PermissionSeeder as SpacePermissionSeeder;
use Modules\Space\Entities\Space;
use Tests\TestCase;

class PitchLocationOverlapTest extends TestCase
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
        $this->seed(EcommerceDefaultSeeder::class);

        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureLoginFromAdminLoginRoute::class,
            \App\Http\Middleware\UserEmailIsVerified::class,
            \App\Http\Middleware\VerifyModule::class,
        ]);
    }

    /** @test */
    public function pitch_date_ranges_overlap_when_days_are_shared(): void
    {
        $service = app(PitchBookingService::class);

        $this->assertTrue($service->pitchDateRangesOverlap('2026-06-01', '2026-06-10', '2026-06-05', '2026-06-15'));
        $this->assertFalse($service->pitchDateRangesOverlap('2026-06-01', '2026-06-10', '2026-06-11', '2026-06-20'));
    }

    /** @test */
    public function city_admin_can_select_a_location_that_already_has_a_non_overlapping_pitch(): void
    {
        [, , $admin, $location] = $this->cityAdminWithLocation('Dam Square');

        $this->createPitchForSpace($admin, $location, [
            'name' => 'Summer Pitch',
            'pitch_started_at' => '2026-07-01',
            'pitch_ended_at' => '2026-07-14',
        ]);

        $options = app(ProductSpaceService::class)->getSpaceOptions();
        $locationOption = $options->firstWhere('id', $location->id);

        $this->assertNotNull($locationOption);
        $this->assertFalse($locationOption['is_disabled']);
    }

    /** @test */
    public function city_admin_cannot_create_overlapping_pitch_at_same_location(): void
    {
        [, , $admin, $location] = $this->cityAdminWithLocation('Dam Square');

        $this->createPitchForSpace($admin, $location, [
            'name' => 'Summer Pitch',
            'pitch_started_at' => '2026-07-01',
            'pitch_ended_at' => '2026-07-14',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.booking.products.store'), [
            'name' => 'Overlapping Pitch',
            'status' => 'draft',
            'description' => '',
            'short_description' => '',
            'roles' => null,
            'is_check_in_required' => false,
            'space_id' => $location->id,
            'pitch_started_at' => '2026-07-10',
            'pitch_ended_at' => '2026-07-20',
            'duration' => '60',
            'bookable_date_range' => 11,
            'timezone' => 'Europe/Amsterdam',
            'weekly_hours' => app(PitchBookingService::class)->defaultWeeklyHours(),
        ]);

        $response->assertSessionHasErrors('space_id');
    }

    /** @test */
    public function city_admin_can_create_non_overlapping_pitch_at_same_location(): void
    {
        [, , $admin, $location] = $this->cityAdminWithLocation('Dam Square');

        $this->createPitchForSpace($admin, $location, [
            'name' => 'Summer Pitch',
            'pitch_started_at' => '2026-07-01',
            'pitch_ended_at' => '2026-07-14',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.booking.products.store'), [
            'name' => 'Autumn Pitch',
            'status' => 'draft',
            'description' => '',
            'short_description' => '',
            'roles' => null,
            'is_check_in_required' => false,
            'space_id' => $location->id,
            'pitch_started_at' => '2026-09-01',
            'pitch_ended_at' => '2026-09-14',
            'duration' => '60',
            'bookable_date_range' => 14,
            'timezone' => 'Europe/Amsterdam',
            'weekly_hours' => app(PitchBookingService::class)->defaultWeeklyHours(),
        ]);

        $response->assertRedirect();
        $this->assertSame(2, Product::where('productable_id', $location->id)->count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createPitchForSpace(User $admin, Space $location, array $overrides): Product
    {
        $payload = array_merge([
            'name' => 'Existing Pitch',
            'status' => 'draft',
            'description' => '',
            'short_description' => '',
            'roles' => null,
            'is_check_in_required' => false,
            'space_id' => $location->id,
            'pitch_started_at' => '2026-07-01',
            'pitch_ended_at' => '2026-07-14',
            'duration' => '60',
            'bookable_date_range' => 14,
            'timezone' => 'Europe/Amsterdam',
            'weekly_hours' => app(PitchBookingService::class)->defaultWeeklyHours(),
        ], $overrides);

        $this->actingAs($admin)->post(route('admin.booking.products.store'), $payload)->assertRedirect();

        return Product::where('productable_id', $location->id)->latest('id')->first();
    }

    /**
     * @return array{0: Space, 1: Space, 2: User, 3: Space}
     */
    private function cityAdminWithLocation(string $locationName): array
    {
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

        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.city_admin'));
        $admin->syncAdminCities([(int) $city->id]);

        $this->actingAs($admin)->post(route('admin.spaces.store'), [
            'name' => $locationName,
            'parent_id' => $citySpace->id,
            'country_code' => 'NL',
            'city_id' => $city->id,
        ])->assertRedirect();

        $location = Space::where('name', $locationName)->first();
        $this->assertNotNull($location);

        return [$country, $citySpace, $admin, $location];
    }
}
