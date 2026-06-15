<?php

namespace Tests\Feature;

use App\Models\GlobalOption;
use Carbon\Carbon;
use Database\Seeders\GlobalOptionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Space\Entities\Space;
use Tests\Concerns\CreatesBookablePitch;
use Tests\TestCase;

/**
 * Public city pages list pitches first; events for a pitch only load after
 * the visitor selects that pitch (FR-NAV-1).
 */
class CityPitchDrillDownTest extends TestCase
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
    public function city_page_renders_sibling_pitch_cards_and_a_pitch_gated_events_section(): void
    {
        $pitchStart = now()->addWeek()->startOfWeek(Carbon::MONDAY);
        $product = $this->createPublishedPitchWithoutProductEvent([
            'pitch_start' => $pitchStart,
            'pitch_end' => $pitchStart->copy()->addDays(13),
            'name' => 'Örebro Slottsplan - Pitch',
            'city_name' => 'Örebro',
            'country_code' => 'SWE',
        ]);

        $countryTypeId = GlobalOption::where('name', 'Country')->value('id');
        $cityTypeId = GlobalOption::where('name', 'City')->value('id');
        $pitchTypeId = GlobalOption::where('name', 'Pitch')->value('id');

        $country = Space::create([
            'name' => 'Sweden',
            'type_id' => $countryTypeId,
            'country_code' => 'SE',
        ]);

        $citySpace = Space::create([
            'name' => 'Örebro',
            'type_id' => $cityTypeId,
            'parent_id' => $country->id,
            'city_id' => $product->city_id,
            'country_code' => 'SE',
        ]);

        // Pitch space lives under Country (sibling to the city), linked only by city_id.
        $pitchSpace = Space::create([
            'name' => 'Örebro Slottsplan',
            'type_id' => $pitchTypeId,
            'parent_id' => $country->id,
            'city_id' => $product->city_id,
            'country_code' => 'SE',
        ]);

        $product->update([
            'productable_type' => Space::class,
            'productable_id' => $pitchSpace->id,
        ]);

        $citySpace->load('page.translations');
        $slugs = $citySpace->page->translate(defaultLocale())->getSlugs();

        $response = $this->get(route('frontend.spaces.show', ['slugs' => $slugs]));

        $response->assertOk();
        $response->assertSee('city-pitch-card', false);
        $response->assertSee('data-pitch-id="'.$pitchSpace->id.'"', false);
        $response->assertSee('id="city-pitch-events"', false);
        $response->assertSee(':require-pitch-selection="true"', false);
        $response->assertSee('Select a pitch above to view its upcoming events.');
    }

    /** @test */
    public function city_page_falls_back_to_events_when_no_pitch_spaces_exist(): void
    {
        $countryTypeId = GlobalOption::where('name', 'Country')->value('id');
        $cityTypeId = GlobalOption::where('name', 'City')->value('id');
        $city = \App\Models\City::factory()->create(['name' => 'Lonely City', 'country_code' => 'SE']);

        $country = Space::create([
            'name' => 'Sweden',
            'type_id' => $countryTypeId,
            'country_code' => 'SE',
        ]);

        $citySpace = Space::create([
            'name' => 'Lonely City',
            'type_id' => $cityTypeId,
            'parent_id' => $country->id,
            'city_id' => $city->id,
            'country_code' => 'SE',
        ]);

        $citySpace->load('page.translations');
        $slugs = $citySpace->page->translate(defaultLocale())->getSlugs();

        $response = $this->get(route('frontend.spaces.show', ['slugs' => $slugs]));

        $response->assertOk();
        $response->assertDontSee('city-pitch-card', false);
        $response->assertDontSee('id="city-pitch-events"', false);
        $response->assertSee('Upcoming Events');
    }
}
