<?php

namespace Modules\Space;

use Illuminate\Http\Request;
use Modules\Space\Entities\Space;

class ModuleService
{
    public static function adminMenus(Request $request): array
    {
        $user = $request->user();
        $canManageSpace = $user->can('viewAny', Space::class);

        return [
            'title' => 'Space',
            'isActive' => $request->routeIs('admin.spaces.*'),
            'isEnabled' => $canManageSpace,
            'children' => [
                [
                    'title' => 'Manage',
                    'link' => route('admin.spaces.index'),
                    'isActive' => $request->routeIs('admin.spaces.index'),
                    'isEnabled' => $canManageSpace,
                ],
            ],
        ];
    }

    public static function permissions(): array
    {
        return [
            'space.*',
            'space.browse',
            'space.read',
            'space.edit',
            'space.add',
            'space.delete',
        ];
    }
}
