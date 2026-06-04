<?php

namespace Tests\Feature;

use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Booking\Entities\Event;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Enums\BookingStatus;
use Modules\Ecommerce\Services\OrderService;
use Tests\TestCase;

class PerformerCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ModuleSeeder::class);
    }

    /** @test */
    public function canceled_events_do_not_block_availability(): void
    {
        $schedule = Schedule::factory()->create();
        $event = Event::factory()->create([
            'schedule_id' => $schedule->id,
            'status' => BookingStatus::UPCOMING->value,
        ]);

        $this->assertTrue(
            Event::query()
                ->where('schedule_id', $schedule->id)
                ->blockingAvailability()
                ->whereKey($event->id)
                ->exists()
        );

        app(OrderService::class)->cancelEvent($event, 'Performer unavailable');

        $this->assertSame(BookingStatus::CANCELED->value, $event->fresh()->status);
        $this->assertFalse(
            Event::query()
                ->where('schedule_id', $schedule->id)
                ->blockingAvailability()
                ->whereKey($event->id)
                ->exists()
        );
    }
}
