<?php

namespace App\Http\Requests;

use App\Rules\MenuItemable;
use App\Rules\ValidUrl;
use App\Services\MenuService;
use App\Services\TranslationService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MenuItemRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $menuItemableRules = [];
        $types = collect(app(MenuService::class)->getMenuItemTypeOptions())
            ->map(function ($type) {
                return $type['id'];
            })
            ->all();

        foreach ($types as $type) {
            if ($type != 'url') {
                $menuItemableRules[] = 'required_if:type,' . $type;
            }
        }

        return [
            'locale' => [
                'sometimes',
                'required',
                Rule::in(app(TranslationService::class)->getLocales()),
            ],
            'title' => [
                'sometimes',
                'required',
                'max:255',
            ],
            'type' => [
                'required',
                'max:18',
                Rule::in($types),
            ],
            'url' => [
                'nullable',
                new ValidUrl(),
            ],
            'is_blank' => [
                'boolean',
            ],
            'menu_itemable_id' => array_merge(
                $menuItemableRules,
                [
                    'nullable',
                    'integer',
                    new MenuItemable($this->get('type'))
                ]
            ),
            'menu_id' => [
                'required',
                'integer',
                'exists:menus,id'
            ],
        ];
    }

    public function messages()
    {
        return [
            'menu_itemable_id.required_if' => __('validation.required_if', [
                'attribute' => __('Menu'),
                'other' => __('Type'),
                'value' => Str::of($this->get('type'))
                    ->title()
                    ->replace('_', ' '),
            ]),
        ];
    }
}
