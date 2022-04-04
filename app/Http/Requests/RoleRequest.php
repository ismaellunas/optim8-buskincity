<?php

namespace App\Http\Requests;

use App\Models\Permission;
use Illuminate\Validation\Rule;

class RoleRequest extends BaseFormRequest
{
    public function rules()
    {
        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique('roles')->ignore($this->role),
            ],
            'permissions' => [
                'array',
                Rule::in(Permission::get('name')->pluck('name')->all()),
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
