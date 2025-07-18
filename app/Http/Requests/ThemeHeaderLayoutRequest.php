<?php

namespace App\Http\Requests;

class ThemeHeaderLayoutRequest extends BaseFormRequest
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
            'layout' => ['required', 'integer'],
            'logo' => [
                'nullable',
                'exists:media,id',
            ],
        ];
    }
}
