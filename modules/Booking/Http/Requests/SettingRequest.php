<?php

namespace Modules\Booking\Http\Requests;

use App\Services\UserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $emailRules = [
            'max:5000',
        ];

        $roleIds = app(UserService::class)
            ->getRoleOptions()
            ->pluck('id')
            ->all();

        return [
            'email_new_booking' => $emailRules,
            'email_reminder' => $emailRules,
            'email_cancellation' => $emailRules,
            'allowed_early_check_in' => [
                'numeric',
                'between:0,60',
            ],
            'check_in_radius'=> [
                'array:value,unit'
            ],
            'check_in_radius.value' => [
                'nullable',
                'numeric',
                'between:0,1000',
            ],
            'check_in_radius.unit' => [
                'required',
                'in:m,km',
            ],
            'access_common_user' => [
                'boolean',
            ],
            'access_roles' => [
                'array',
                'nullable',
            ],
            'access_roles.*' => [
                'numeric',
                Rule::in($roleIds),
            ],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
