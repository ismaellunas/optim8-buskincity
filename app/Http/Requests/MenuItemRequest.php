<?php

namespace App\Http\Requests;

use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Foundation\Http\FormRequest;

class MenuItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return RuleFactory::make([
            '%title%' => 'sometimes|required',
            'type' => 'required',
            'url' => 'nullable',
            'page_id' => 'nullable',
            'post_id' => 'nullable',
            'category_id' => 'nullable',
            'menu_id' => 'required',
        ]);
    }
}
