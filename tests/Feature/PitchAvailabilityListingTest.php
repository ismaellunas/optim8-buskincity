<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBookablePitch;
use Tests\TestCase;

/**
 * Target behavior for FR-BOOK-3 (T8.3).
 *
 * Expected to FAIL until performer index lists pitches instead of ProductEvents.
 *
 * @group events-overhaul
 */
class PitchAvailabilityListingTest extends TestCase
{
    use CreatesBookablePitch;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedEventsOverhaulDependencies();
        $this->withoutFrontendBookingMiddleware();
    }

    /** @test */
    public function performer_pitch_list_includes_published_pitch_without_admin_product_event(): void
    {
        $pitchStart = now()->addWeek()->startOfWeek(Carbon::MONDAY);
        $product = $this->createPublishedPitchWithoutProductEvent([
            'name' => 'Festival Test',
            'pitch_start' => $pitchStart,
            'pitch_end' => $pitchStart->copy()->addDays(13),
        ]);

        $this->actingAsPerformerOnUserPortal();

        $response = $this->get(route('booking.products.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Booking::FrontendProductIndex', false)
            ->has('events.data', 1)
            ->where('events.data.0.pitch_name', 'Festival Test')
        );
    }

    /** @test */
    public function draft_pitch_is_excluded_from_performer_list(): void
    {
        $product = $this->createPublishedPitchWithoutProductEvent(['name' => 'Hidden Draft']);
        $product->status = 'draft';
        $product->save();

        $this->actingAsPerformerOnUserPortal();

        $this->get(route('booking.products.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Booking::FrontendProductIndex', false)
                ->has('events.data', 0)
            );
    }
}
