<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UserUpdateRequest extends UserStoreRequest
{
    protected $errorBag = 'userUpdate';

    public function authorize()
    {
        return auth()->user()->can('update', $this->route('user'));
    }

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
                Rule::unique('users')->where(function ($query) {
                    $query->whereNull('deleted_at')
                        ->where('id', '<>', $this->route('user')->id);
                })
            ],
            'photo' => [
                'nullable',
                'mimes:' . implode(',', config('constants.extensions.image')),
                'max:'.config('constants.file_size.profile_picture'),
            ],
            'language_id' => ['required', 'exists:App\Models\Language,id'],
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
