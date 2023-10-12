<?php

namespace Modules\Booking;

use App\Contracts\ManageableModuleInterface;
use App\Contracts\ToggleableModuleStatusInterface;
use App\Models\User;
use App\Services\BaseModuleService;
use App\Traits\ActivateableModuleStatus;
use App\Traits\ManageableModule;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Booking\Events\ModuleDeactivated;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\ModuleService as EcommerceModuleService;

class ModuleService extends BaseModuleService implements
    ManageableModuleInterface,
    ToggleableModuleStatusInterface
{
    use ManageableModule;
    use ActivateableModuleStatus;

    public function menuPermissions(User $user): array
    {
        return [
            'admin.booking.products.index' => $user->can('viewAny', Product::class),
            'admin.booking.orders.index' => $user->can('viewAny', Order::class),
            'admin.booking.settings.edit' => $user->isAdministrator,
        ];
    }

    public function defaultNavigations(): array
    {
        return [
            [
                'route' => 'admin.booking.products.index',
                'routeIs' => 'admin.booking.products.index',
                'title' => ":Booking_term.products",
                'default' => true,
            ],
            [
                'route' => 'admin.booking.orders.index',
                'routeIs' => 'admin.booking.orders.index',
                'title' => ":Booking_term.bookings",
                'default' => true,
            ],
            [
                'route' => 'admin.booking.settings.edit',
                'routeIs' => 'admin.booking.settings.edit',
                'title' => "Settings",
                'default' => true,
            ],
        ];
    }

    public static function frontendMenus(Request $request): array
    {
        return [
            [
                'title' => Str::title(__(":booking_term.products")),
                'link' => route('booking.products.index'),
                'isActive' => $request->routeIs('booking.products.index'),
                'isEnabled' => true,
            ],
            [
                'title' => Str::title(__(':booking_term.bookings')),
                'link' => route('booking.orders.index'),
                'isActive' => $request->routeIs('booking.orders.index'),
                'isEnabled' => true,
            ],
        ];
    }

    public static function widgets(): array
    {
        return [
            'latestBooking',
        ];
    }

    public static function centerCoordinate(): array
    {
        return [
            'latitude' => 59.3260668,
            'longitude' => 17.8419716
        ];
    }

    public static function permissions(): Collection
    {
        return app(EcommerceModuleService::class)->permissions();
    }

    public function adminPermissions(): array
    {
        return [
            'product.*',
            'order.*',
        ];
    }

    public function deactivationEventClass(): ?string
    {
        return ModuleDeactivated::class;
    }

    public function deactivationMessages(): array
    {
        return [
            __("All permissions assigned to users from the :module module will be unassigned from those users.", [
                'module' => $this->model()->title,
            ]),
            __("Product items managed by :module module will be set to draft.", ['module' => $this->model()->title]),
            __("Any upcoming and ongoing booked product items will be canceled."),
            __("Users who are assigned as managers will be unassigned from product."),
        ];
    }
}
