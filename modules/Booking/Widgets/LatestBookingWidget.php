<?php

namespace Modules\Booking\Widgets;

use App\Contracts\WidgetInterface;
use Modules\Ecommerce\Services\OrderService;

class LatestBookingWidget implements WidgetInterface
{
    private $user;

    private $componentName = "LatestBooking";
    private $title = "Latest bookings";
    private $baseRouteName = "admin.booking.orders";

    public function __construct($request)
    {
        $this->user = $request->user();
    }

    public function data(): array
    {
        $orderService = app(OrderService::class);

        return [
            'title' => __($this->title),
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
            'i18n' => [
                'status' => __('Status'),
                'name' => __('Name'),
                'user' => __('User'),
                'date' => __('Date'),
                'time' => __('Time'),
                'location' => __('Location'),
                'any' => __('Any'),
                'view_detail' => __('View detail'),
                'view_all' => __('View all'),
                'no_data' => __('No data'),
                'search' => __('Search'),
            ]
        ];
    }

    public function canBeAccessed(): bool
    {
        return $this->user->can('order.browse')
            || $this->user->products->isNotEmpty();
    }
}
