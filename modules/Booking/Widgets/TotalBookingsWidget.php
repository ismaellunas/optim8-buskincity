<?php

namespace Modules\Booking\Widgets;

use App\Contracts\WidgetInterface;
use App\Models\User;
use Modules\Booking\Enums\BookingStatus;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Services\OrderService;

class TotalBookingsWidget implements WidgetInterface
{
    private $vueComponent = 'TotalWidget';
    private $vueComponentModule = null;
    private array $storedSetting;
    private User $user;

    public function __construct(array $storedSetting)
    {
        $this->storedSetting = $storedSetting;
        $this->user = auth()->user();
    }

    private function url(): string
    {
        return route('admin.api.widget.data', [
            'uuid' => $this->storedSetting['uuid']
        ]);
    }

    private function viewUrl($queryParams = [])
    {
        return route('admin.booking.orders.index', $queryParams);
    }

    public function data(): array
    {
        return [
            'title' => $this->storedSetting['title'] ?? 'Bookings',
            'url' => $this->url(),
            'module' => null,
            'vueComponent' => $this->vueComponent,
            'vueComponentModule' => $this->vueComponentModule,
            'grid' => $storedSetting['grid'] ?? 6,
            'backgroudColor' => $this->storedSetting['background_color'],
        ];
    }

    public function canBeAccessed(): bool
    {
        return (
            $this->user->can('viewAny', Order::class)
            || $this->user->isProductManager()
        );
    }

    public function response()
    {
        $orderService = app(OrderService::class);

        return response()->json([
            'totals' => [
                [
                    'text' => $orderService->getWidgetTotalUpcomingBooking(),
                    'url' => $this->viewUrl(['status' => [BookingStatus::UPCOMING->value]])
                ],
                [
                    'text' => $orderService->getWidgetTotalBooking(),
                    'url' => $this->viewUrl(),
                ]
            ]
        ]);
    }
}
