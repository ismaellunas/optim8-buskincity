<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThemeHeaderLayoutRequest extends FormRequest
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
            'logo.file' => [
                'nullable',
                'file',
                'max:'.config('constants.one_megabyte') * 50,
                'mimes:jpeg,jpg,png',
            ],
        ];
    }

    public function attributes()
    {
        return [
            'logo.file' => 'Logo',
        ];
    }
}
