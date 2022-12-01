<?php

namespace App\Http\Requests;

use App\Rules\ValidUrl;
use App\Services\MenuService;
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
        $types = collect(app(MenuService::class)->getMenuItemTypeOptions(true))
            ->map(function ($type) {
                return $type['id'];
            })
            ->all();

        return [
            'locale' => [
                'required',
                Rule::in(app(TranslationService::class)->getLocales()),
            ],
            'menu_items.*.title' => [
                'sometimes',
                'required',
                'max:255',
            ],
            'menu_items.*.type' => [
                'sometimes',
                'required',
                'max:18',
                Rule::in($types),
            ],
            'menu_items.*.url' => [
                'nullable',
                new ValidUrl(),
            ],
            'menu_items.*.is_blank' => [
                'boolean',
            ],
            'menu_items.*.menu_itemable_id' => [
                'nullable',
                'integer',
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
            'menu_itemable_id',
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
