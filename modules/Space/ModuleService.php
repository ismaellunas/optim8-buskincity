<?php

namespace Modules\Space;

use App\Models\GlobalOption;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Space\Entities\Space;

class ModuleService
{
    public static function adminMenus(Request $request): array
    {
        $user = $request->user();
        $canManageSpace = $user->can('viewAny', Space::class);

        return [
            'title' => __("Space"),
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
                    'isEnabled' => $user->can('viewAny', GlobalOption::class),
                ],
            ],
        ];
    }

    public static function permissions(): Collection
    {
        return collect([
            'space.*',
            'space.browse',
            'space.read',
            'space.edit',
            'space.add',
            'space.delete',
        ]);
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

    public static function defaultLogoUrl(): string
    {
        return StorageService::getImageUrl(
            config('constants.default_images.logo_space')
        );
    }

    public static function maxParentDepth(): int
    {
        return 2;
    }
}
