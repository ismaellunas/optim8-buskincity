<?php

namespace App\Http\Requests;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\Role;
use Illuminate\Validation\Rule;

class UserStoreRequest extends BaseFormRequest
{
    use PasswordValidationRules;

    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:128'],
            'last_name' => ['required', 'string', 'max:128'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->where(function ($query) {
                    $query->whereNull('deleted_at');
                })
            ],
            'password' => $this->passwordRules(),
            'role' => [
                'nullable',
                Rule::in($this->getRoleIds()),
            ],
            'photo' => [
                'nullable',
                'mimes:' . implode(',', config('constants.extensions.image')),
                'max:'.config('constants.file_size.profile_picture'),
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
