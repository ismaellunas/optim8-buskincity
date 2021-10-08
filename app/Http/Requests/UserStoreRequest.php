<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserStoreRequest extends FormRequest
{
    use PasswordValidationRules;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->route('user'))
            ],
            'password' => $this->passwordRules(),
            'role' => [
                'nullable',
                Rule::in($this->getRoleIds),
            ],
        ];
    }

    protected function getRoleIds(): array
    {
        $query = Role::select('id');

        if (! auth()->user()->hasRole(config('permission.super_admin_role'))) {
            $query->withoutSuperAdmin();
        }

        return $query->pluck('id')->all();
    }
}
