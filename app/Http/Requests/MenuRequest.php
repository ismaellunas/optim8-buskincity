<?php

namespace App\Http\Requests;

use App\Models\MenuItem;
use App\Rules\ValidUrl;
use App\Services\TranslationService;
use Illuminate\Validation\Rule;

class MenuRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'locale' => [
                'required',
                Rule::in(TranslationService::getLocales()),
            ],
            'menu_items.*.title' => [
                'sometimes',
                'required',
                'max:255',
            ],
            'menu_items.*.type' => [
                'sometimes',
                'required',
                'integer',
                Rule::in(array_keys(MenuItem::TYPE_VALUES)),
            ],
            'menu_items.*.url' => [
                'nullable',
                new ValidUrl(),
            ],
            'menu_items.*.is_blank' => [
                'boolean',
            ],
            'menu_items.*.page_id' => [
                'nullable',
                'integer',
                'exists:pages,id'
            ],
            'menu_items.*.post_id' => [
                'nullable',
                'integer',
                'exists:posts,id'
            ],
            'menu_items.*.category_id' => [
                'nullable',
                'integer',
                'exists:categories,id'
            ],
            'menu_items.*.menu_id' => [
                'required',
                'integer',
                'exists:menus,id'
            ],
        ];
    }

    protected function customAttributes(): array
    {
        $attr = [];
        $columns = [
            'locale',
            'title',
            'type',
            'is_blank',
            'page_id',
            'post_id',
            'category_id',
            'menu_id',
            'url',
        ];

        foreach ($columns as $column) {
            for ($i = 0; $i < count($this['menu_items']); $i++) {
                $attr["menu_items.".$i.".".$column] = ucwords(str_replace('_', ' ', $column))." (".strtoupper($this['locale']).") ";
            }
        }

        return $attr;
    }
}
