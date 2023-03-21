<?php

namespace Modules\Ecommerce\Services;

use App\Models\User;
use App\Services\CountryService;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Lunar\Base\OrderReferenceGeneratorInterface;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Booking\Entities\Event;
use Modules\Booking\Entities\Scopes\WithBookingCityScope;
use Modules\Booking\Entities\Scopes\WithBookingStatusScope;
use Modules\Booking\Enums\BookingStatus;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Enums\OrderLineType;
use Modules\Ecommerce\Enums\OrderStatus;

class OrderService
{
    private function recordBuilder(
        string $term = null,
        ?array $scopes = null
    ): Builder {
        $user = auth()->user();

        $queryBuilder = $this->conditionsBuilder($user, $term, $scopes);

        return $this->columnsBuilder($queryBuilder);
    }

    private function conditionsBuilder(
        User $user,
        string $term = null,
        ?array $scopes = null
    ): Builder {
        $isUserProductManager = $user->isProductManager();

        return Order::when($term, function ($query) use ($term) {
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
                        } elseif ($scopeName == 'dateRange') {
                            $query->whereHas(
                                'firstEventLine.latestEvent',
                                function (Builder $query) use ($scopeName, $value) {
                                    $query->$scopeName($value);
                                }
                            );
                        } elseif ($scopeName == 'city') {
                            $query->whereHas(
                                'firstEventLine.purchasable.product',
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
            ->when($isUserProductManager, function ($query) use ($user) {
                $query->productManager($user->id);
            });
    }

    private function columnsBuilder(Builder $query): Builder
    {
        return $query->with([
            'firstEventLine' => function ($query) {
                $query->with([
                    'latestEvent' => function ($query) {
                        $query->select(['id', 'order_line_id', 'schedule_id', 'booked_at', 'duration', 'duration_unit', 'status']);
                        $query->with('schedule:id,timezone');
                    },
                    'purchasable' => function ($query) {
                        $query->with('product', function ($query) {
                            $query->select('id', 'product_type_id', 'attribute_data');
                            $query->with([
                                'metas' => function ($query) {
                                    $query->whereIn('key', ['locations']);
                                },
                                'managers' => function ($query) {
                                    $query->select('user_id');
                                }
                            ]);
                        });
                    },
                ]);
            },
            'user:id,email,first_name,last_name,deleted_at',
        ])
        ->select(
            'id',
            'user_id',
            'status',
            'placed_at',
        )
        ->scoped(new WithBookingStatusScope());
    }

    public function getRecords(
        User $user,
        string $term = null,
        ?array $scopes = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $records = $this
            ->recordBuilder($term, $scopes)
            ->with('checkIn:id,checked_in_at,order_id,user_id')
            ->paginate($perPage);

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
            ->with('checkIn:id,checked_in_at,order_id,user_id')
            ->where('user_id', $user->id)
            ->paginate($perPage);

        $this->transformRecords($records, $user);

        return $records;
    }

    public function getWidgetRecords(
        User $user,
        string $term = null,
        ?array $scopes = null,
        int $limit = 10,
    ): Collection {
        $records = $this->recordBuilder($term, $scopes)
            ->scoped(new WithBookingStatusScope())
            ->latest()
            ->limit($limit)
            ->get();

        $this->transformWidgetRecords($records, $user);

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
                'status' => Str::title($record->booking_status),
                'start_end_time' => $event->displayStartEndTime,
                'date' => $event->timezonedBookedAt->format('d M Y'),
                'timezone' => $event->timezonedBookedAt->format('P'),
                'event' => [
                    'date' => $event->timezonedBookedAt->format('d F Y'),
                    'duration' => $event->displayDuration,
                    'start_end_time' => $event->displayStartEndTime,
                    'timezone' => $event->schedule->timezone,
                ],
                'check_in_time' => $record->hasCheckIn()
                    ? $record->checkIn
                        ->checked_in_at
                        ->setTimezone($event->schedule->timezone)
                        ->format('H:i')
                    : null,
                'city' => $product->locations[0]['city'] ?? null,
                'can' => [
                    'cancel' => $user->can('cancelBooking', $record),
                    'reschedule' => $user->can('rescheduleBooking', $record),
                ],
            ];
        });
    }

    public function transformWidgetRecords($records, $user)
    {
        $records->transform(function ($record) use ($user) {
            $event = $record->firstEventLine->latestEvent;

            $product = $record->firstEventLine->purchasable->product;

            return (object) [
                'id' => $record->id,
                'product_name' => $product->displayName,
                'city' => $product->locations[0]['city'] ?? null,
                'customer_name' => $record->user->fullName ?? null,
                'status' => Str::title($record->booking_status),
                'start_end_time' => $event->displayStartEndTime,
                'date' => $event->timezonedBookedAt->format('d M Y'),
                'can' => [
                    'read' => $user->can('view', $record)
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

        $product = $order->firstProduct;

        $product->load(['metas' => function ($query) {
            $query->whereIn('key', ['locations', 'is_check_in_required']);
        }]);

        $carbonTimeZone = CarbonTimeZone::create($event->schedule->timezone);

        return [
            'id' => $order->id,
            'product' => [
                'id' => $product->id,
                'name' => $product->displayName,
                'is_check_in_required' => (bool) $product->is_check_in_required,
            ],
            'location' => function () use ($product) {
                $location = collect($product->locations[0] ?? null);

                if ($location->has('country_code')) {
                    $location['country_name'] = app(CountryService::class)->getCountryName(
                        $location['country_code']
                    );
                }

                $location['direction_url'] = app(ProductEventService::class)
                    ->getGoogleMapDirectionUrl($product);

                return $location->only('address',
                    'country_name',
                    'city',
                    'direction_url',
                    'latitude',
                    'longitude'
                );
            },
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
                'timezoneOffset' => 'GMT '.$carbonTimeZone->toOffsetName(),
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

    public function getWidgetTotalBooking(): int
    {
        return $this->conditionsBuilder(auth()->user())->count();
    }

    public function getWidgetTotalUpcomingBooking(): int
    {
        return $this->conditionsBuilder(auth()->user(), null, [
            'inStatus' => [BookingStatus::UPCOMING->value],
        ])->count();
    }

    public function statusOptions(
        User $user,
        ?array $scopes = null,
        ?string $noneLabel = null
    ): Collection {
        $statuses = $this
            ->conditionsBuilder($user, null, $scopes)
            ->scoped(new WithBookingStatusScope())
            ->distinct()
            ->get();

        $options = BookingStatus::options()
            ->filter(fn ($option) => $statuses->contains('booking_status', $option['id']));

        if (! is_null($noneLabel)) {
            $options->prepend(['id' => null, 'value' => $noneLabel]);
        }

        return $options;
    }

    public function cityOptions(
        User $user,
        ?array $scopes = null
    ): Collection {
        $options = $this
            ->conditionsBuilder($user, null, $scopes)
            ->scoped(new WithBookingCityScope())
            ->distinct()
            ->get()
            ->pluck('city')
            ->filter();

        return $options;
    }
}
