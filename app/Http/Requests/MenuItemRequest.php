<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'locale' => 'required|max:15',
            'title' => 'required|max:255',
            'type' => 'required|max:255',
            'url' => 'nullable',
            'page_id' => 'nullable',
            'post_id' => 'nullable',
            'category_id' => 'nullable',
            'menu_id' => 'required',
        ];
    }
}
