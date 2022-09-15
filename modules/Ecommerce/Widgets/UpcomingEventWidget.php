<?php

namespace Modules\Ecommerce\Widgets;

use App\Contracts\WidgetInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Ecommerce\Entities\Event;

class UpcomingEventWidget implements WidgetInterface
{
    private $user;

    private $componentName = "EventUpcoming";
    private $title = "Upcoming Events";

    public function __construct($request)
    {
        $this->user = $request->user();
    }

    public function data(): array
    {
        return [
            'title' => $this->title,
            'componentName' => $this->componentName,
            'moduleName' => config('ecommerce.name'),
            'data' => [
                'records' => $this->getRecords(),
            ],
        ];
    }

    public function canBeAccessed(): bool
    {
        return $this->user->hasRole([config('permission.role_names.performer')]);
    }

    private function getRecords(): Collection
    {
        $events = Event::upcoming()
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
            ->orderBy('booked_at', 'ASC')
            ->limit(10)
            ->get();

        $events->transform(function ($event) {
            return [
                'order_id' => $event->orderLine->order_id,
                'name' => Str::limit($event->orderLine->purchasable->product->displayName, 35, '...'),
                'booked_at' => $event->booked_at->format(config('ecommerce.format.date_event_widget_record')),
                'timezone' => $event->schedule->timezone,
                'url' => route('ecommerce.orders.show', $event->orderLine->order_id),
            ];
        });

        return $events;
    }
}
