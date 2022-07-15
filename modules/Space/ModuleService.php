<?php

namespace Modules\Space;

class ModuleService
{
    public static function adminMenus(): array
    {
        return [
            'title' => 'Space',
            'isActive' => request()->routeIs('admin.spaces.*'),
            'isEnabled' => true,//$user->can('system.language'),
            'children' => [
                [
                    'title' => 'Manage',
                    'link' => route('admin.spaces.index'),
                    'isActive' => request()->routeIs('admin.spaces.index'),
                    'isEnabled' => true,//$user->can('system.language'),
                ],
            ],
        ];
    }
}
