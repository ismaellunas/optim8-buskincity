<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\GlobalOption;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\GlobalOptionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Space\Database\Seeders\PermissionSeeder as SpacePermissionSeeder;
use Modules\Space\Entities\Space;
use Tests\Concerns\CreatesBookablePitch;
use Tests\TestCase;

class ScopedAdminBookingsTest extends TestCase
{
    use CreatesBookablePitch;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedEventsOverhaulDependencies();
        $this->seed(GlobalOptionSeeder::class);
        $this->seed(SpacePermissionSeeder::class);

        $this->withoutMiddleware([
            \App\Http\Middleware\EnsureLoginFromAdminLoginRoute::class,
            \App\Http\Middleware\UserEmailIsVerified::class,
            \App\Http\Middleware\VerifyModule::class,
        ]);
    }

    /** @test */
    public function scoped_admin_space_edit_hides_events_tab(): void
    {
        [$citySpace, $admin] = $this->cityAdminWithCitySpace();

        $this->actingAs($admin)
            ->get(route('admin.spaces.edit', $citySpace->id))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('can.events.view', false)
            );
    }

    /** @test */
    public function city_admin_can_view_bookings_page_and_records(): void
    {
        [$citySpace, $admin] = $this->cityAdminWithBookedPerformance();

        $this->actingAs($admin)
            ->get(route('admin.bookings.index'))
            ->assertOk();

        $response = $this->actingAs($admin)
            ->getJson(route('admin.bookings.records'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['performer', 'pitch', 'started_at', 'ended_at', 'status'],
            ],
        ]);
        $response->assertJsonMissing(['record_type', 'actions']);
        $this->assertNotEmpty($response->json('data'));
    }

    /** @test */
    public function global_admin_cannot_access_scoped_bookings_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.admin'));

        $this->actingAs($admin)
            ->get(route('admin.bookings.index'))
            ->assertForbidden();
    }

    /** @test */
    public function special_events_admin_can_view_bookings_in_scoped_city(): void
    {
        [$citySpace, , $product] = $this->cityAdminWithBookedPerformance();

        $seAdmin = User::factory()->create();
        $seAdmin->assignRole(config('permission.role_names.special_events_admin'));
        $seAdmin->syncScopeCities(
            config('permission.role_names.special_events_admin'),
            [(int) $product->city_id]
        );

        $this->actingAs($seAdmin)
            ->get(route('admin.bookings.index'))
            ->assertOk();

        $this->actingAs($seAdmin)
            ->getJson(route('admin.bookings.records'))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /**
     * @return array{0: Space, 1: User}
     */
    private function cityAdminWithCitySpace(): array
    {
        $countryTypeId = GlobalOption::where('name', 'Country')->value('id');
        $cityTypeId = GlobalOption::where('name', 'City')->value('id');
        $city = City::factory()->create(['name' => 'Borås', 'country_code' => 'SWE']);

        $country = Space::create([
            'name' => 'Sweden',
            'type_id' => $countryTypeId,
            'country_code' => 'SE',
        ]);

        $citySpace = Space::create([
            'name' => 'Borås',
            'type_id' => $cityTypeId,
            'parent_id' => $country->id,
            'city_id' => $city->id,
            'country_code' => 'SE',
        ]);

        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.city_admin'));
        $admin->syncAdminCities([$city->id]);

        return [$citySpace, $admin];
    }

    /**
     * @return array{0: Space, 1: User, 2: \Modules\Ecommerce\Entities\Product}
     */
    private function cityAdminWithBookedPerformance(): array
    {
        $pitchStart = now()->addWeek()->startOfWeek(Carbon::MONDAY);
        $product = $this->createPublishedPitchWithoutProductEvent([
            'pitch_start' => $pitchStart,
            'pitch_end' => $pitchStart->copy()->addDays(13),
            'name' => 'Borås Konstmuseum - Pitch',
            'city_name' => 'Borås',
            'country_code' => 'SWE',
        ]);

        $countryTypeId = GlobalOption::where('name', 'Country')->value('id');
        $cityTypeId = GlobalOption::where('name', 'City')->value('id');

        $country = Space::create([
            'name' => 'Sweden',
            'type_id' => $countryTypeId,
            'country_code' => 'SE',
        ]);

        $citySpace = Space::create([
            'name' => 'Borås',
            'type_id' => $cityTypeId,
            'parent_id' => $country->id,
            'city_id' => $product->city_id,
            'country_code' => 'SE',
        ]);

        $admin = User::factory()->create();
        $admin->assignRole(config('permission.role_names.city_admin'));
        $admin->syncAdminCities([(int) $product->city_id]);

        $bookDate = $this->nextBookableWeekday($pitchStart->copy()->addDays(2));
        Carbon::setTestNow($bookDate->copy()->setTime(8, 0));

        $this->actingAsPerformerOnUserPortal();

        $this->post(route('booking.orders.book-event', $product), [
            'date' => $bookDate->toDateString(),
            'time' => '10:00',
        ])->assertRedirect();

        return [$citySpace, $admin, $product];
    }
}
