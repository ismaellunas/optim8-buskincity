<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslationUpdateRequest extends FormRequest
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
        $groups = collect(config('constants.translations.groups'))
            ->keys()
            ->all();

        return [
            'translations.*.locale' => [
                'required',
                'max: 15',
                Rule::in($locale)
            ],
            'translations.*.group' => [
                'nullable',
                'max: 127',
                Rule::in($groups)
            ],
            'translations.*.key' => [
                'required'
            ],
            'translations.*.value' => [
                'nullable',
            ],
        ];
    }

    public function attributes(): array
    {
        $attr = [];
        $columns = [
            'locale',
            'group',
            'key',
            'value',
        ];

        foreach ($columns as $column) {
            foreach ($this['translations'] as $index => $value) {
                $attr["translations.".$index.".".$column] = ucwords(str_replace('_', ' ', $column));
            }
        }

        return $attr;
    }
}
