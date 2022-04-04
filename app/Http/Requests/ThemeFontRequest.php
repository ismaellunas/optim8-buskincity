<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ThemeFontRequest extends BaseFormRequest
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
        $fontRule = ['nullable', 'string'];

        return [
            'uppercase_text' => [
                'array',
                Rule::in(config('constants.theme_uppercases')),
            ],
            'content_paragraph_width' => [
                'required',
                'min:0',
                'integer',
            ],

            'headings_font_family' => $fontRule,
            'headings_font_weight' => $fontRule,
            'headings_font_style' => $fontRule,

            'main_text_font_family' => $fontRule,
            'main_text_font_weight' => $fontRule,
            'main_text_font_style' => $fontRule,

            'buttons_font_family' => $fontRule,
            'buttons_font_weight' => $fontRule,
            'buttons_font_style' => $fontRule,
        ];
    }

    protected function customAttributes(): array
    {
        $attributes = [];

        $providedAttributes = [
            'uppercase_text',
            'content_paragraph_width',
            'headings_font_family',
            'headings_font_weight',
            'headings_font_style',
            'main_text_font_family',
            'main_text_font_weight',
            'main_text_font_style',
            'buttons_font_family',
            'buttons_font_weight',
            'buttons_font_style',
        ];

        foreach ($providedAttributes as $providedAttribute) {
            $attributes[$providedAttribute] = Str::title(
                Str::replace('_', ' ', $providedAttribute)
            );
        }

        return $attributes;
    }
}
