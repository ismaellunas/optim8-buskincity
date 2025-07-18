<?php

namespace App\Http\Requests;

class SocialMediaRequest extends BaseFormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'icon' => [
                'required',
                'max:100',
            ],
            'url' => [
                'required',
                'url',
                'max:255',
            ],
            'is_blank' => [
                'boolean',
            ],
        ];
    }
}
