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
                'url',
            ],
            'page_id' => [
                'nullable',
                'integer',
                'exists:pages,id'
            ],
            'post_id' => [
                'nullable',
                'integer',
                'exists:posts,id'
            ],
            'category_id' => [
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
}
