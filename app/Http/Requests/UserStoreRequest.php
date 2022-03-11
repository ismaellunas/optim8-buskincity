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
            'first_name' => ['required', 'string', 'max:128'],
            'last_name' => ['required', 'string', 'max:128'],
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
                Rule::in($this->getRoleIds()),
            ],
            'photo' => [
                'nullable',
                'mimes:jpg,jpeg,png',
                'max:'.config('constants.one_megabyte') * 1
            ],
            'language_id' => ['required', 'exists:App\Models\Language,id'],
        ];
    }

    protected function getRoleIds(): array
    {
        $query = Role::select('id');

        if (! auth()->user()->hasRole(config('permission.super_admin_role'))) {
            $query->withoutSuperAdmin();
        }

        return $query->get()->pluck('id')->all();
    }
}
