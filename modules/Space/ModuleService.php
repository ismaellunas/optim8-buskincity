<?php

namespace Modules\Space;

use Illuminate\Http\Request;
use Modules\Space\Entities\Space;

class ModuleService
{
    const MEDIA_TYPE_LOGO = 11;
    const MEDIA_TYPE_COVER = 12;

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
                [
                    'title' => 'Settings',
                    'link' => route('admin.spaces.settings.index'),
                    'isActive' => $request->routeIs('admin.spaces.settings.index'),
                    'isEnabled' => (
                        $user->isSuperAdministrator || $user->isAdministrator
                    ),
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

    public static function mediaFolder(): string
    {
        return 'space';
    }

    public static function mediaTypes(): array
    {
        return [
            'logo' => self::MEDIA_TYPE_LOGO,
            'cover' => self::MEDIA_TYPE_COVER,
        ];
    }

    public static function maxLengths(): array
    {
        return [
            'description' => 65000,
            'surface' => 500,
            'excerpt' => 500,
            'condition' => 500,
        ];
    }
}
