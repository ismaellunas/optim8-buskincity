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

        return [
            'title' => 'Booking',
            'isActive' => $request->routeIs('admin.booking.*'),
            'isEnabled' => true,
            'children' => [
                [
                    'title' => 'Products',
                    'link' => route('admin.ecommerce.products.index'),
                    'isActive' => $request->routeIs('admin.ecommerce.products.index'),
                    'isEnabled' => $user->can('viewAny', Product::class)
                ],
                [
                    'title' => 'Orders',
                    'link' => route('admin.ecommerce.orders.index'),
                    'isActive' => $request->routeIs('admin.ecommerce.products.index'),
                    'isEnabled' => $user->can('viewAny', Order::class)
                ],
                [
                    'title' => 'Settings',
                    'link' => route('admin.booking.settings.edit'),
                    'isActive' => $request->routeIs('admin.booking.settings.edit'),
                    'isEnabled' => $user->hasRole(['Administrator', 'Super Administrator']),
                ],
            ],
        ];
    }

    public static function frontendMenus(Request $request): array
    {
        return [
            [
                'title' => 'Products',
                'link' => route('ecommerce.products.index'),
                'isActive' => $request->routeIs('ecommerce.products.index'),
                'isEnabled' => true,
            ],
            [
                'title' => 'Orders',
                'link' => route('ecommerce.orders.index'),
                'isActive' => $request->routeIs('ecommerce.products.index'),
                'isEnabled' => true,
            ],
        ];
    }
}
