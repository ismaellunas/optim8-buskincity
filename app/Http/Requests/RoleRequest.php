<?php

namespace App\Http\Requests;

use App\Services\RoleService;
use Illuminate\Validation\Rule;

class RoleRequest extends BaseFormRequest
{
    public function rules()
    {
        $permissions = [];

        collect(app(RoleService::class)->getPermissionOptions())
            ->each
            ->each(function ($perm) use (&$permissions) {
                $permissions[] = $perm['value'];
            });

        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique('roles')->ignore($this->role),
            ],
            'permissions' => [
                'array',
                Rule::in($permissions),
            ]
        ];
    }

    protected function customAttributes(): array
    {
        return [
            'name' => __('Role Name'),
        ];
    }
}
