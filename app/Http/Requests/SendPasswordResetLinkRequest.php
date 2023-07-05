<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Validation\Rule;

class SendPasswordResetLinkRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('managePasswordResetEmail', User::class);
    }

    public function rules()
    {
        $userService = app(UserService::class);

        return [
            'content' => [
                'required',
                'max:5000',
            ],
            'subject' => [
                'required',
                'max:950',
            ],
            'expiry' => [
                'required',
                Rule::in($userService->passwordResetExpiryOptions()->pluck('id')),
            ],
            'users' => [
                'required',
                'array',
            ],
        ];
    }

    protected function customAttributes(): array
    {
        return [
            'content' => __('Content'),
            'expiry' => __('Expiry'),
            'subject' => __('Subject'),
        ];
    }
}
