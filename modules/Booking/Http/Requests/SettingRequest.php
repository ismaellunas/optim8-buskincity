<?php

namespace Modules\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
