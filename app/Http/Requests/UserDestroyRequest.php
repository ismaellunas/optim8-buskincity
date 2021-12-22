<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserDestroyRequest extends FormRequest
{
    protected $errorBag = 'deleteUser';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_reassigned' => [
                'required',
            ],
            'assigned_user' => [
                'nullable',
                'required_if:is_reassigned,true',
                Rule::exists('users', 'id')->where(function ($query) {
                    return $query->where('id', '<>', $this->route('user')->id);
                }),
            ],
        ];
    }

    public function attributes()
    {
        return [
            'assigned_user' => __('User'),
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_user.required_if' => __('validation.required', ['attribute' => __('User')]),
        ];
    }
}
