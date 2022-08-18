<?php

namespace Modules\Booking;

use Illuminate\Http\Request;

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
                    'isEnabled' => $user->hasRole(['Administrator', 'Super Administrator']),
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
}
