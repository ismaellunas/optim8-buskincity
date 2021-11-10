<?php

namespace App\Http\Requests;

use App\Models\MenuItem;
use App\Services\TranslationService as TranslationSv;
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

    public function attributes()
    {
        $attr = [];
        $locales = TranslationSv::getLocaleOptions();
        $columns = [
            'locale',
            'title',
            'type',
            'page_id',
            'post_id',
            'category_id',
            'menu_id',
        ];

        foreach ($locales as $locale) {
            foreach ($columns as $column) {
                for ($i = 0; $i < count($this[$locale['id']]); $i++) {
                    $attr[$locale['id'].".".$i.".".$column] = ucwords(str_replace('_', ' ', $column))." (".$locale['name'].") ";
                }
            }
        }

        return $attr;
    }
}
