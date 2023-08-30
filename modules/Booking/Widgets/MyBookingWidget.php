<?php

namespace Modules\Booking\Widgets;

use App\Contracts\WidgetInterface;
use App\Entities\Widgets\BaseWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Booking\Entities\Event;

class MyBookingWidget extends BaseWidget implements WidgetInterface
{
    private $records = [];

    protected $componentModule = "MyBooking";

    public function __construct(array $storedSetting)
    {
        parent::__construct($storedSetting);

        $this->records = $this->getRecords();
    }

    protected function getData(): array
    {
        return [
            'records' => $this->records,
        ];
    }

    public function canBeAccessed(): bool
    {
        return parent::canBeAccessed()
            && (
                $this->user->hasRole([config('permission.role_names.performer')])
                && $this->records->isNotEmpty()
            );
    }

    private function getRecords(): Collection
    {
        $events = Event::passed()
            ->with([
                'orderLine' => function ($query) {
                    $query->select('id', 'order_id', 'purchasable_id', 'purchasable_type');
                    $query->with('purchasable', function ($query) {
                        $query->with('product', function ($query) {
                            $query->select('id', 'attribute_data');
                        });
                    });
                },
                'schedule' => function ($query) {
                    $query->select('id', 'timezone');
                },
            ])
            ->whereHas('orderLine.order', function ($query) {
                $query->where('user_id', $this->user->id);
            })
            ->orderBy('booked_at', 'DESC')
            ->limit(10)
            ->get();

        $events->transform(function ($event) {
            $product = $event->orderLine->purchasable->product;

            return [
                'order_id' => $event->orderLine->order_id,
                'name' => Str::limit($product->displayName, 35, '...'),
                'city' => $product->displayCity,
                'country' => $product->displayCountry,
                'booked_at' => $event->booked_at->format(config('ecommerce.format.date_event_widget_record')),
                'timezone' => $event->schedule->timezone,
                'url' => route('booking.orders.show', $event->orderLine->order_id),
            ];
        });

        return $events;
    }
}
