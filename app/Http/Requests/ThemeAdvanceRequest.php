<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;

class ThemeAdvanceRequest extends BaseFormRequest
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
            'home_page' => ['nullable', 'exists:pages,id'],
            'additional_css' => ['nullable', 'string'],
            'additional_javascript' => ['nullable', 'string'],
            'tracking_code_inside_head' => ['nullable', 'string'],
            'tracking_code_after_body' => ['nullable', 'string'],
            'tracking_code_before_body' => ['nullable', 'string'],
        ];
    }

    protected function customAttributes(): array
    {
        $attributes = collect([
            'home_page',
            'additional_css',
            'additional_javascript',
            'tracking_code_inside_head',
            'tracking_code_after_body',
            'tracking_code_before_body',
        ]);

        return $attributes->mapWithKeys(function ($attribute, $key) {
            $title = Str::replace('_', ' ', $attribute);

            return [$attribute => Str::title($title)];
        })->all();
    }
}
