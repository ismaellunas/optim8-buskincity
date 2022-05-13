<?php

namespace App\Http\Requests;

use App\Models\MenuItem;
use App\Rules\ValidUrl;
use App\Services\TranslationService;
use Illuminate\Validation\Rule;

class MenuItemRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'locale' => [
                'sometimes',
                'required',
                Rule::in(TranslationService::getLocales()),
            ],
            'title' => [
                'sometimes',
                'required',
                'max:255',
            ],
            'type' => [
                'required',
                'integer',
                Rule::in(array_keys(MenuItem::TYPE_VALUES)),
            ],
            'url' => [
                'nullable',
                new ValidUrl(),
            ],
            'is_blank' => [
                'boolean',
            ],
            'page_id' => [
                'required_if:type,'.MenuItem::TYPE_PAGE,
                'nullable',
                'integer',
                'exists:pages,id'
            ],
            'post_id' => [
                'required_if:type,'.MenuItem::TYPE_POST,
                'nullable',
                'integer',
                'exists:posts,id'
            ],
            'category_id' => [
                'required_if:type,'.MenuItem::TYPE_CATEGORY,
                'nullable',
                'integer',
                'exists:categories,id'
            ],
            'menu_id' => [
                'required',
                'integer',
                'exists:menus,id'
            ],
        ];
    }

    public function messages()
    {
        $typeValue = __(MenuItem::TYPE_VALUES[$this->get('type')] ?? "");
        $otherAttribute = __('Type');

        return [
            'page_id.required_if' => __('validation.required_if', [
                'attribute' => __('Page'),
                'other' => $otherAttribute,
                'value' => $typeValue,
            ]),
            'post_id.required_if' => __('validation.required_if', [
                'attribute' => __('Post'),
                'other' => $otherAttribute,
                'value' => $typeValue,
            ]),
            'category_id.required_if' => __('validation.required_if', [
                'attribute' => __('Category'),
                'other' => $otherAttribute,
                'value' => $typeValue,
            ])
        ];
    }
}
