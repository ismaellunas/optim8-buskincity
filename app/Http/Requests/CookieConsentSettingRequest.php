<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CookieConsentSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'is_enabled' => [
                'required',
                'boolean',
            ],
            'message' => [
                'required',
                'max: 1000',
            ],
            'message_decline' => [
                'required',
                'max: 1000',
            ],
            'redirect_decline_page_id' => [
                'nullable',
                'exists:App\Models\Page,id'
            ],
        ];
    }
}
