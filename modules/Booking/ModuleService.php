<?php

namespace Modules\Booking;

use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\Product;

class ModuleService
{
    public static function adminMenus(Request $request): array
    {
        $user = $request->user();

        $children = collect([
            [
                'title' => __('Products'),
                'link' => route('admin.booking.products.index'),
                'isActive' => $request->routeIs('admin.booking.products.index'),
                'isEnabled' => $user->can('viewAny', Product::class)
            ],
            [
                'title' => __('Bookings'),
                'link' => route('admin.booking.orders.index'),
                'isActive' => $request->routeIs('admin.booking.orders.index'),
                'isEnabled' => $user->can('viewAny', Order::class)
            ],
            [
                'title' => __('Settings'),
                'link' => route('admin.booking.settings.edit'),
                'isActive' => $request->routeIs('admin.booking.settings.edit'),
                'isEnabled' => $user->hasRole(['Administrator', 'Super Administrator']),
            ],
        ]);

        return [
            'title' => __('Booking'),
            'isActive' => $request->routeIs('admin.booking.*'),
            'isEnabled' => $children->contains('isEnabled', true),
            'children' => $children->all(),
        ];
    }

    public static function frontendMenus(Request $request): array
    {
        return [
            [
                'title' => 'Products',
                'link' => route('booking.products.index'),
                'isActive' => $request->routeIs('booking.products.index'),
                'isEnabled' => true,
            ],
            [
                'title' => 'Bookings',
                'link' => route('booking.orders.index'),
                'isActive' => $request->routeIs('booking.orders.index'),
                'isEnabled' => true,
            ],
        ];
    }


    public static function frontendWidgets(): array
    {
        return [
            'upcomingEvent',
            'lastEvent',
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
}
