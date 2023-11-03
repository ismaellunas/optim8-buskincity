<?php

namespace Modules\Space;

use App\Contracts\ManageableModuleInterface;
use App\Contracts\ToggleableModuleStatusInterface;
use App\Models\GlobalOption;
use App\Models\User;
use App\Services\BaseModuleService;
use App\Services\StorageService;
use App\Traits\ActivateableModuleStatus;
use App\Traits\ManageableModule;
use Illuminate\Support\Collection;
use Modules\Booking\ModuleService as BookingModuleService;
use Modules\Space\Entities\Space;
use Modules\Space\Events\ModuleDeactivated;

class ModuleService extends BaseModuleService implements
    ManageableModuleInterface,
    ToggleableModuleStatusInterface
{
    use ManageableModule;
    use ActivateableModuleStatus;

    public function menuPermissions(User $user): array
    {
        return [
            'admin.spaces.index' => $user->can('viewAny', Space::class),
            'admin.spaces.settings.index' => $user->can('viewAny', GlobalOption::class),
        ];
    }

    public function defaultNavigations(): array
    {
        return [
            [
                'route' => 'admin.spaces.index',
                'routeIs' => 'admin.spaces.index',
                'title' => __('Manage'),
                'default' => true,
            ],
            [
                'route' => 'admin.spaces.settings.index',
                'routeIs' => 'admin.spaces.settings.index',
                'title' => __('Settings'),
                'default' => true,
            ]
        ];
    }

    public function adminPermissions(): array
    {
        return [
            'space.*',
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

    public function deactivationEventClass(): ?string
    {
        return ModuleDeactivated::class;
    }

    public function deactivationMessages(): array
    {
        return [
            __("All permissions assigned to users for :module module will be unassigned from users in the system.", [
                'module' => $this->model()->title,
            ]),
            __("Users who are assigned as managers will be unassigned from space module."),
            __("Pages managed by space will be set to draft."),
            __("Events managed by space will be set to draft."),
            __("Page builder components currently in use that are related to the :module module will be deleted.", [
                'module' => $this->model()->title,
            ]),
            __("Spaces will be unassigned from the :resource in the :module module.", [
                'resource' => __(':booking_term.products'),
                'module' => app(BookingModuleService::class)->model()->title,
            ])
        ];
    }
}
