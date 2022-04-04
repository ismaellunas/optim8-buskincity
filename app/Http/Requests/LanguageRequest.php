<?php

namespace App\Http\Requests;

use App\Services\LanguageService;
use Illuminate\Validation\Rule;

class LanguageRequest extends BaseFormRequest
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
        $languageIds = app(LanguageService::class)->getShownLanguageOptions()
            ->pluck('id')
            ->all();

        return [
            'languages' => [
                'array',
            ],
            'languages.*' => [
                Rule::in($languageIds),
            ],
            'default_language' => [
                'in_array:languages.*',
            ]
        ];
    }

    protected function customAttributes(): array
    {
        return [
            'languages.*' => __('validation.attributes.languages'),
        ];
    }
}
