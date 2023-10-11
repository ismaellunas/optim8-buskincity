<?php

namespace Modules\FormBuilder;

use App\Contracts\ManageableModuleInterface;
use App\Models\User;
use App\Services\BaseModuleService;
use App\Traits\ManageableModule;
use Illuminate\Support\Collection;
use Modules\FormBuilder\Services\FormBuilderService;

class ModuleService extends BaseModuleService implements ManageableModuleInterface
{
    use ManageableModule;

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
}