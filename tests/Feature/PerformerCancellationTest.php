<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\ProductType;
use Lunar\Models\TaxClass;
use Modules\Booking\Entities\Event;
use Modules\Booking\Entities\Schedule;
use Modules\Booking\Entities\ScheduleRule;
use Modules\Booking\Entities\ScheduleRuleTime;
use Modules\Booking\Enums\BookingStatus;
use Modules\Booking\Services\EventService;
use Modules\Ecommerce\Database\Seeders\DefaultSeeder;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\OrderLine;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Entities\ProductVariant;
use Modules\Ecommerce\Enums\OrderLineType;
use Modules\Ecommerce\Enums\OrderStatus;
use Modules\Ecommerce\Enums\ProductStatus;
use Modules\Ecommerce\Services\OrderService;
use Tests\TestCase;

class PerformerCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ModuleSeeder::class);

        if (! ProductType::where('name', 'Event')->exists()) {
            $this->seed(DefaultSeeder::class);
        }

        Setting::factory()->create([
            'key' => 'booking_access_common_user',
            'display_name' => null,
            'value' => true,
            'group' => 'booking',
            'order' => 3,
        ]);
    }

    /** @test */
    public function canceled_events_no_longer_block_schedule_availability(): void
    {
        [$schedule, $bookedAt] = $this->createScheduleWithSlot();

        $eventService = app(EventService::class);
        $date = $bookedAt->toDateString();

        $this->assertContains('10:00', $eventService->availableTimes($schedule, $date)->all());

        $event = Event::factory()->create([
            'schedule_id' => $schedule->id,
            'booked_at' => $bookedAt,
            'duration' => 30,
            'duration_unit' => 'minute',
            'status' => BookingStatus::UPCOMING->value,
        ]);

        $this->assertNotContains('10:00', $eventService->availableTimes($schedule, $date)->all());

        app(OrderService::class)->cancelEvent($event, 'Performer unavailable');

        $event->refresh();
        $this->assertSame(BookingStatus::CANCELED->value, $event->status);
        $this->assertContains('10:00', $eventService->availableTimes($schedule, $date)->all());
    }

    /** @test */
    public function performer_who_placed_the_order_can_cancel_it(): void
    {
        [$schedule, $bookedAt, $order, $performer] = $this->createBookedOrder();

        $this->assertTrue($performer->can('cancelBooking', $order));

        $this->actingAs($performer)
            ->post(route('booking.orders.cancel', $order), [
                'message' => 'Cannot make it',
            ])
            ->assertRedirect(route('booking.orders.index'));

        $order->refresh();
        $event = $order->firstEventLine->latestEvent;

        $this->assertSame(OrderStatus::CANCELED->value, $order->status);
        $this->assertSame(BookingStatus::CANCELED->value, $event->status);

        $availableTimes = app(EventService::class)
            ->availableTimes($schedule, $bookedAt->toDateString());

        $this->assertContains('10:00', $availableTimes->all());
    }

    /** @test */
    public function another_user_cannot_cancel_someone_elses_booking(): void
    {
        [, , $order] = $this->createBookedOrder();

        $otherUser = User::factory()->create();

        $this->assertFalse($otherUser->can('cancelBooking', $order));

        $this->actingAs($otherUser)
            ->post(route('booking.orders.cancel', $order), [
                'message' => 'Should fail',
            ])
            ->assertForbidden();
    }

    /**
     * @return array{0: Schedule, 1: Carbon}
     */
    private function createScheduleWithSlot(): array
    {
        $bookedAt = now()->next(Carbon::TUESDAY)->setTime(10, 0);

        $product = Product::create([
            'product_type_id' => ProductType::where('name', 'Event')->first()->id,
            'status' => ProductStatus::PUBLISHED->value,
            'attribute_data' => [
                'name' => new TranslatedText(collect([
                    'en' => new Text('Test Pitch'),
                ])),
            ],
        ]);

        $product->setMeta([
            'duration' => 30,
            'duration_unit' => 'minute',
        ]);

        $schedule = Schedule::factory()->create([
            'timezone' => 'UTC',
            'schedulable_type' => Product::class,
            'schedulable_id' => $product->id,
        ]);

        $rule = ScheduleRule::factory()
            ->for($schedule)
            ->dayOfWeek($bookedAt->dayOfWeekIso)
            ->available()
            ->create();

        ScheduleRuleTime::factory()->for($rule)->create([
            'started_time' => '10:00',
            'ended_time' => '12:00',
        ]);

        return [$schedule->fresh('schedulable'), $bookedAt];
    }

    /**
     * @return array{0: Schedule, 1: Carbon, 2: Order, 3: User}
     */
    private function createBookedOrder(): array
    {
        [$schedule, $bookedAt] = $this->createScheduleWithSlot();
        $performer = User::factory()->create();

        $product = $schedule->schedulable;

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'tax_class_id' => TaxClass::getDefault()->id,
            'purchasable' => 'always',
            'shippable' => false,
            'stock' => 0,
            'backorder' => 0,
            'sku' => 'EVENT-'.$product->id,
        ]);

        $order = Order::factory()->create([
            'user_id' => $performer->id,
            'channel_id' => Channel::getDefault()->id,
            'currency_code' => Currency::getDefault()->code,
            'compare_currency_code' => Currency::getDefault()->code,
            'status' => OrderStatus::COMPLETED->value,
        ]);

        OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_type' => get_class($variant),
            'purchasable_id' => $variant->id,
            'type' => OrderLineType::EVENT->value,
            'identifier' => $variant->sku,
            'unit_price' => 0,
            'unit_quantity' => 1,
            'quantity' => 1,
            'sub_total' => 0,
            'discount_total' => 0,
            'tax_total' => 0,
            'total' => 0,
        ]);

        Event::factory()->create([
            'schedule_id' => $schedule->id,
            'order_line_id' => $order->fresh()->firstEventLine->id,
            'booked_at' => $bookedAt,
            'duration' => 30,
            'duration_unit' => 'minute',
            'status' => BookingStatus::UPCOMING->value,
        ]);

        return [$schedule, $bookedAt, $order->fresh(['firstEventLine.latestEvent']), $performer];
    }
}
