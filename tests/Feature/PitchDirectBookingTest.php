<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Modules\Booking\Entities\Event;
use Modules\Ecommerce\Entities\Order;
use Tests\Concerns\CreatesBookablePitch;
use Tests\TestCase;

/**
 * Target behavior for FR-BOOK-1 / FR-BOOK-2 (T8.2).
 *
 * Expected to FAIL until admin ProductEvent is removed from the booking path.
 *
 * @group events-overhaul
 */
class PitchDirectBookingTest extends TestCase
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
    public function performer_can_book_pitch_without_admin_product_event(): void
    {
        $pitchStart = now()->addWeek()->startOfWeek(Carbon::MONDAY);
        $product = $this->createPublishedPitchWithoutProductEvent([
            'pitch_start' => $pitchStart,
            'pitch_end' => $pitchStart->copy()->addDays(13),
        ]);

        $bookDate = $this->nextBookableWeekday($pitchStart->copy()->addDays(2));

        Carbon::setTestNow($bookDate->copy()->setTime(8, 0));

        $this->actingAsPerformerOnUserPortal();

        $response = $this->post(route('booking.orders.book-event', $product), [
            'date' => $bookDate->toDateString(),
            'time' => '10:00',
        ]);

        $response->assertRedirect(route('booking.orders.index'));

        $this->assertDatabaseHas('events', [
            'status' => 'upcoming',
        ]);

        $this->assertSame(1, Event::count());
        $this->assertSame(1, Order::count());
    }

    /** @test */
    public function performer_can_batch_book_pitch_without_admin_product_event(): void
    {
        $pitchStart = now()->addWeek()->startOfWeek(Carbon::MONDAY);
        $product = $this->createPublishedPitchWithoutProductEvent([
            'pitch_start' => $pitchStart,
            'pitch_end' => $pitchStart->copy()->addDays(13),
        ]);

        $bookDate = $this->nextBookableWeekday($pitchStart->copy()->addDays(2));

        Carbon::setTestNow($bookDate->copy()->setTime(8, 0));

        $this->actingAsPerformerOnUserPortal();

        $response = $this->post(route('booking.orders.book-event-batch', $product), [
            'slots' => [
                ['date' => $bookDate->toDateString(), 'time' => '10:00'],
                ['date' => $bookDate->toDateString(), 'time' => '11:00'],
            ],
        ]);

        $response->assertRedirect(route('booking.orders.index'));
        $this->assertSame(2, Event::count());
        $this->assertSame(2, Order::count());
    }

    /** @test */
    public function booked_event_does_not_require_product_event_id_column(): void
    {
        $pitchStart = now()->addWeek()->startOfWeek(Carbon::MONDAY);
        $product = $this->createPublishedPitchWithoutProductEvent([
            'pitch_start' => $pitchStart,
            'pitch_end' => $pitchStart->copy()->addDays(13),
        ]);

        $bookDate = $this->nextBookableWeekday($pitchStart->copy()->addDays(2));
        Carbon::setTestNow($bookDate->copy()->setTime(8, 0));

        $this->actingAsPerformerOnUserPortal();

        $this->post(route('booking.orders.book-event', $product), [
            'date' => $bookDate->toDateString(),
            'time' => '10:00',
        ])->assertRedirect();

        $event = Event::first();
        $this->assertNotNull($event);
        $this->assertFalse(Schema::hasColumn('events', 'product_event_id'));
    }
}
