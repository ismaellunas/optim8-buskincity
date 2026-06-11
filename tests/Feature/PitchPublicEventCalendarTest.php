<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesBookablePitch;
use Tests\TestCase;

/**
 * Target behavior for FR-BOOK-4 (T8.4).
 *
 * Expected to FAIL until pitch show exposes public booked-event calendar data.
 *
 * @group events-overhaul
 */
class PitchPublicEventCalendarTest extends TestCase
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
    public function pitch_show_includes_upcoming_booked_events_for_guests(): void
    {
        $pitchStart = now()->addWeek()->startOfWeek(Carbon::MONDAY);
        $product = $this->createPublishedPitchWithoutProductEvent([
            'pitch_start' => $pitchStart,
            'pitch_end' => $pitchStart->copy()->addDays(13),
        ]);

        $bookDate = $this->nextBookableWeekday($pitchStart->copy()->addDays(2));
        Carbon::setTestNow($bookDate->copy()->setTime(8, 0));

        $performer = $this->actingAsPerformerOnUserPortal();

        $this->post(route('booking.orders.book-event', $product), [
            'date' => $bookDate->toDateString(),
            'time' => '10:00',
        ])->assertRedirect();

        $response = $this->get(route('booking.products.show', $product));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Booking::FrontendProductShow', false)
            ->has('bookedEvents', 1)
            ->where('bookedEvents.0.date', $bookDate->toDateString())
            ->where('bookedEvents.0.time', '10:00')
        );
    }
}
