<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UserUpdateRequest extends UserStoreRequest
{
    public function rules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:128'],
            'last_name' => ['required', 'string', 'max:128'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->route('user')->id)
            ],
            'photo' => [
                'nullable',
                'mimes:jpg,jpeg,png',
                'max:'.config('constants.one_megabyte') * 1
            ],
        ];

        if ($this->user->isSuperAdministrator) {
            $rules['role'] = ['prohibited'];
        } else {
            $rules['role'] = [
                'nullable',
                Rule::in($this->getRoleIds()),
            ];
        }

        return $rules;
    }
}
