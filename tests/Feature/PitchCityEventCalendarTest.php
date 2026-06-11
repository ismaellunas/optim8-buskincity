<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\GlobalOption;
use Carbon\Carbon;
use Database\Seeders\GlobalOptionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Space\Entities\Space;
use Tests\Concerns\CreatesBookablePitch;
use Tests\TestCase;

/**
 * City/country space pages should list booked pitch performances (FR-BOOK-4).
 */
class PitchCityEventCalendarTest extends TestCase
{
    use CreatesBookablePitch;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedEventsOverhaulDependencies();
        $this->seed(GlobalOptionSeeder::class);
        $this->withoutFrontendBookingMiddleware();
    }

    /** @test */
    public function city_space_events_api_lists_booked_pitch_performances_by_city_id(): void
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

        $bookDate = $this->nextBookableWeekday($pitchStart->copy()->addDays(2));
        Carbon::setTestNow($bookDate->copy()->setTime(8, 0));

        $this->actingAsPerformerOnUserPortal();

        $this->post(route('booking.orders.book-event', $product), [
            'date' => $bookDate->toDateString(),
            'time' => '10:00',
        ])->assertRedirect();

        $response = $this->getJson(route('api.space.space-events', [
            'encryptedSpaceId' => encrypt($citySpace->id),
        ]));

        $response->assertOk();
        $response->assertJsonPath('records.data.0.title', fn ($title) => is_string($title) && $title !== '');
        $response->assertJsonFragment([
            'started_at' => $bookDate->copy()->setTime(10, 0)->format('d M Y H:i'),
        ]);
    }
}
