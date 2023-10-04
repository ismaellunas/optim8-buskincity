<?php

namespace Modules\FormBuilder;

use App\Contracts\ManageableModuleInterface;
use App\Contracts\ToggleableModuleStatusInterface;
use App\Models\User;
use App\Services\BaseModuleService;
use App\Traits\ActivateableModuleStatus;
use App\Traits\ManageableModule;
use Illuminate\Support\Collection;
use Modules\FormBuilder\Events\ModuleDeactivated;
use Modules\FormBuilder\Services\FormBuilderService;

class ModuleService extends BaseModuleService implements
    ManageableModuleInterface,
    ToggleableModuleStatusInterface
{
    use ManageableModule;
    use ActivateableModuleStatus;

    public function menuPermissions(User $user): array
    {
        return [
            'admin.form-builders.index' => $user->can('form_builder.browse'),
        ];
    }

    public function defaultNavigations(): array
    {
        return [
            [
                'route' => 'admin.form-builders.index',
                'routeIs' => 'admin.form-builders.index',
                'title' => __('Manage'),
                'default' => true,
            ],
        ];
    }

    public static function permissions(): Collection
    {
        return collect(config('formbuilder.permissions'));
    }

    public function adminPermissions(): array
    {
        return [ 'form_builder.*' ];
    }

    public static function activeOptions()
    {
        return [
            [
                'id' => true,
                'value' => 'Active'
            ],
            [
                'id' => false,
                'value' => 'Inactive'
            ]
        ];
    }

    public static function getAdminData(): array
    {
        return [
            'formOptions' => app(FormBuilderService::class)->getFormOptions(),
        ];
    }

    public static function widgets(): array
    {
        return [
            'entry',
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
            __("Notifications managed by form builder module will be set to inactive."),
        ];
    }
}
