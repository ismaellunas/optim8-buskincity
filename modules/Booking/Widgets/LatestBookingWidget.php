<?php

namespace Modules\Booking\Widgets;

use App\Contracts\WidgetInterface;
use Modules\Booking\Enums\BookingStatus;
use Modules\Booking\Services\ProductEventService;

class LatestBookingWidget implements WidgetInterface
{
    private $user;

    private $componentName = "LatestBooking";
    private $title = "Latest Bookings";
    private $baseRouteName = "admin.booking.orders";
    private $productEventService;

    public function __construct($request)
    {
        $this->user = $request->user();

        $this->productEventService = new ProductEventService();
    }

    public function data(): array
    {
        return [
            'title' => $this->title,
            'componentName' => $this->componentName,
            'moduleName' => config('booking.name'),
            'data' => [
                'baseRouteName' => $this->baseRouteName,
                'statusOptions' => BookingStatus::options(),
                'cityOptions' => $this->productEventService->getCityOptions(),
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
