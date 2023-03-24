<?php

namespace Modules\Booking\Widgets;

use App\Contracts\WidgetInterface;
use Modules\Ecommerce\Services\OrderService;

class LatestBookingWidget implements WidgetInterface
{
    private $user;

    private $componentName = "LatestBooking";
    private $title = "Latest Bookings";
    private $baseRouteName = "admin.booking.orders";

    public function __construct($request)
    {
        $this->user = $request->user();
    }

    public function data(): array
    {
        $orderService = app(OrderService::class);

        return [
            'title' => $this->title,
            'componentName' => $this->componentName,
            'moduleName' => config('booking.name'),
            'data' => [
                'baseRouteName' => $this->baseRouteName,
                'statusOptions' => $orderService->statusOptions(
                    $this->user,
                    null,
                    __('Status')
                ),
                'locationOptions' => $orderService->getLocationOptions(
                    $this->user,
                    null
                ),
            ],
            'order' => 1,
        ];
    }

    public function canBeAccessed(): bool
    {
        return $this->user->can('order.browse')
            || $this->user->products->isNotEmpty();
    }
}
