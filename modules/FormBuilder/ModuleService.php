<?php

namespace Modules\FormBuilder;

use Illuminate\Http\Request;
use Modules\FormBuilder\Services\FormBuilderService;

class ModuleService
{
    public static function permissions()
    {
        return config('formbuilder.permissions');
    }

    public static function adminMenus(Request $request): array
    {
        $user = $request->user();
        $canManageFormBuilder = $user->can('form_builder.browse');

        return [
            'title' => 'Form Builder',
            'isActive' => $request->routeIs('admin.form-builders.*'),
            'isEnabled' => $canManageFormBuilder,
            'children' => [
                [
                    'title' => 'Manage',
                    'link' => route('admin.form-builders.index'),
                    'isActive' => $request->routeIs('admin.form-builders.index'),
                    'isEnabled' => $canManageFormBuilder,
                ],
            ],
        ];
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

    public static function defaultSettings(): array
    {
        return [
            'submit_text' => 'Submit',
        ];
    }
}