<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslationRequest extends FormRequest
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
        $locale = config('translatable.locales');
        $group = config('constants.translations.groups');

        return [
            'locale' => [
                'required',
                'max: 255',
                Rule::in($locale)
            ],
            'group' => [
                'required',
                'max: 255',
                Rule::in($group)
            ],
            'key' => [
                'required'
            ],
            'value' => [
                'nullable'
            ],
        ];
    }
}
