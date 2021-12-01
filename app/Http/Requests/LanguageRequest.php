<?php

namespace App\Http\Requests;

use App\Services\LanguageService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LanguageRequest extends FormRequest
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
        $languageIds = collect(app(LanguageService::class)->getShownLanguageOptions())
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
                Rule::in($languageIds),
            ]
        ];
    }
}
