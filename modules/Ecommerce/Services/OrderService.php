<?php

namespace Modules\Ecommerce\Services;

use App\Models\User;
use App\Services\MediaService;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use GetCandy\Base\OrderReferenceGeneratorInterface;
use GetCandy\Models\Channel;
use GetCandy\Models\Currency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Entities\Event;
use Modules\Ecommerce\Enums\BookingStatus;
use Modules\Ecommerce\Enums\OrderLineType;
use Modules\Ecommerce\Enums\OrderStatus;

class OrderService
{
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    private function recordBuilder(
        string $term = null,
        ?array $scopes = null
    ): Builder {
        return Order::orderBy('reference', 'DESC')
            ->when($term, function ($query) use ($term) {
                $query
                    ->where('reference', 'ILIKE', '%'.$term.'%')
                    ->orWhere('status', 'ILIKE', '%'.$term.'%')
                    ->orWhereHas('user', function (Builder $query) use ($term) {
                        $query->search($term);
                    })
                    ->orWhereHas('firstEventLine.purchasable.product', function (Builder $query) use ($term) {
                        $query->searchWithoutScout($term);
                    });
            })
            ->when($scopes, function ($query, $scopes) {
                foreach ($scopes as $scopeName => $value) {
                    $query->when($value, function ($query, $value) use ($scopeName) {
                        if ($scopeName == 'inStatus') {
                            $query->whereHas(
                                'firstEventLine.latestEvent',
                                function (Builder $query) use ($scopeName, $value) {
                                    $query->$scopeName($value);
                                }
                            );
                        } else {
                            $query->$scopeName($value);
                        }
                    });
                }
            })
            ->with([
                'firstEventLine.latestEvent.schedule',
                'firstEventLine.purchasable.product' => function ($query) {
                    $query->select('id', 'product_type_id', 'attribute_data');
                },
                'user' => function ($query) {
                    $query->select('id', 'email', 'first_name', 'last_name');
                },
            ])
            ->select(
                'id',
                'user_id',
                'status',
                'placed_at',
            );
    }

    public function getRecords(
        User $user,
        string $term = null,
        ?array $scopes = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $records = $this->recordBuilder($term, $scopes)->paginate($perPage);

        $this->transformRecords($records, $user);

        return $records;
    }

    public function getFrontendRecords(
        User $user,
        string $term = null,
        ?array $scopes = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $records = $this
            ->recordBuilder($term, $scopes)
            ->where('user_id', $user->id)
            ->paginate($perPage);

        $this->transformRecords($records, $user);

        return $records;
    }

    public function transformRecords($records, $user)
    {
        $records->getCollection()->transform(function ($record) use ($user) {
            $event = $record->firstEventLine->latestEvent;

            $product = $record->firstEventLine->purchasable->product;

            return (object) [
                'id' => $record->id,
                'product_name' => $product->displayName,
                'customer_name' => $record->user->fullName ?? null,
                'reference' => $record->reference,
                'status' => Str::title($event->status),
                'start_end_time' => $event->displayStartEndTime,
                'date' => $event->timezonedBookedAt->format('d M Y'),
                'event' => [
                    'date' => $event->timezonedBookedAt->format('d F Y'),
                    'duration' => $event->displayDuration,
                    'start_end_time' => $event->displayStartEndTime,
                    'timezone' => $event->schedule->timezone,
                ],
                'can' => [
                    'cancel' => $user->can('cancel', $record),
                    'reschedule' => $user->can('reschedule', $record),
                ],
            ];
        });
    }

    public function getRecord(Order $order): array
    {
        $record = $this->getFrontendRecord($order);

        $record['user_full_name'] = $order->user->fullName;

        return $record;
    }

    public function getFrontendRecord(Order $order): array
    {
        $event = $order->firstEventLine->latestEvent;

        $product = $order->firstEventLine->purchasable->product;

        $carbonTimeZone = CarbonTimeZone::create($event->schedule->timezone);

        return [
            'id' => $order->id,
            'product' => [
                'id' => $product->id,
                'name' => $product->displayName,
            ],
            'event' => [
                'date' => $event->timezonedBookedAt->format('d F Y'),
                'duration' => $event->displayDuration,
                'duration_details' => [
                    'duration' => $event->duration,
                    'unit' => $event->duration_unit,
                ],
                'start_end_time' => $event->displayStartEndTime,
                'time' => $event->timezonedBookedAt->format('H:i'),
                'status' => Str::title($event->status),
                'timezone' => $event->schedule->timezone,
                'timezoneOffset' => 'UTC '.$carbonTimeZone->toOffsetName(),
            ],
        ];
    }

    public function cancelOrder(Order $order)
    {
        $order->status = OrderStatus::CANCELED->value;
        $order->save();
    }

    public function cancelEvent(Event $booking, ?string $message = null)
    {
        $booking->status = BookingStatus::CANCELED->value;
        $booking->message = $message;
        $booking->save();
    }

    public function rescheduleEvent(
        Event $event,
        Carbon $dateTime,
        ?string $message = null
    ): Event {
        $newEvent = $event->replicate();

        $newEvent->booked_at = $dateTime->format('Y-m-d H:i');
        $newEvent->status = BookingStatus::UPCOMING->value;
        $newEvent->save();

        $event->status = BookingStatus::RESCHEDULED->value;
        $event->message = $message;
        $event->save();

        return $newEvent;
    }

    public function bookEvent(Product $product, Carbon $dateTime, User $user): Order
    {
        $currency = Currency::getDefault();
        $channel = Channel::getDefault();
        $generator = app(OrderReferenceGeneratorInterface::class);

        $lines = collect();

        $variant = $product->variants->first();

        $lines->push([
            'purchasable_type' => get_class($variant),
            'purchasable_id' => $variant->id,
            'type' => OrderLineType::EVENT,
            'description' => "",
            'option' => null,
            'identifier' => $variant->sku,
            'unit_price' => 0,
            'unit_quantity' => 0,
            'quantity' => 1,
            'sub_total' => 0,
            'discount_total' => 0,
            'tax_breakdown' => [],
            'tax_total' => 0,
            'total' =>	0,
        ]);

        $order = [
            'user_id' => $user->id,
            'channel_id' => $channel->id,
            'status' => OrderStatus::COMPLETED,
            'sub_total' => 0,
            'tax_breakdown' => [],
            'tax_total' => 0,
            'total' => 0,
            'currency_code' => $currency->code,
            'compare_currency_code' => $currency->code,
            'placed_at' => Carbon::now(),
            'meta' => [
                'product_id' => $product->id,
                'sku' => $variant->sku,
                'booked_at' => $dateTime,
                'duration' => $product->duration,
                'duration_unit' => 'minute',
            ],
        ];

        $orderModel = Order::factory()->create($order);
        $orderModel->reference = $generator->generate($orderModel);
        $orderModel->save();

        $orderModel->lines()->createMany($lines->toArray());

        $orderLine = $orderModel->lines->first();

        $schedule = $product->eventSchedule;

        Event::factory()->state([
            'schedule_id' => $schedule->id,
            'order_line_id' => $orderLine->id,
            'booked_at' => $dateTime,
            'duration' => $product->duration,
            'duration_unit' => $product->duration_unit,
            'status' => BookingStatus::UPCOMING,
            'timezone' => $schedule->timezone,
        ])->create();

        return $orderModel;
    }

    public function emailReceipients(Order $order): Collection
    {
        $recipients = User::inRoleNames([config('permission.role_names.admin')])
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->map(function ($user) {
                return (object)[
                    'name'  => $user->fullName,
                    'email' => $user->email,
                ];
            });

        $recipients->push((object) [
            'name' => $order->user->fullName,
            'email' => $order->user->email,
        ]);

        return $recipients;
    }
}
