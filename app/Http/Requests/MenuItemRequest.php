<?php

namespace App\Http\Requests;

use App\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            '*.*.locale' => [
                'sometimes',
                'required',
                'max:15',
                Rule::in(config('constants.locale')),
            ],
            '*.*.title' => [
                'sometimes',
                'required',
                'max:255',
            ],
            '*.*.type' => [
                'sometimes',
                'required',
                'max:255',
                Rule::in(MenuItem::TYPES),
            ],
            '*.*.url' => 'nullable',
            '*.*.page_id' => [
                'nullable',
                'integer',
                'exists:pages,id'
            ],
            '*.*.post_id' => [
                'nullable',
                'integer',
                'exists:posts,id'
            ],
            '*.*.category_id' => [
                'nullable',
                'integer',
                'exists:categories,id'
            ],
            '*.*.menu_id' => [
                'required',
                'integer',
                'exists:menus,id'
            ],
        ];
    }

    public function messages()
    {
        $rule = [];
        $columns = ['locale', 'title', 'type'];
        $validations = ['required'];
        $locales = config('constants.locale');

        foreach ($locales as $locale) {
            foreach ($validations as $validation) {
                foreach ($columns as $column) {
                    $rule[$locale.".*.".$column.".".$validation] = ucwords($column)." (".strtoupper($locale).") is ".$validation;
                }
            }
        }

        return $rule;
    }
}
