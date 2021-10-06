<?php

namespace App\Http\Requests;

use App\Models\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => [
                'required',
                Rule::unique('roles')->ignore($this->role),
            ],
            'permissions' => [
                'array',
                Rule::in(Permission::get('name')->pluck('name')->all()),
            ]
        ];
    }

    public function attributes()
    {
        return [
            'name' => __('Role Name'),
        ];
    }
}
