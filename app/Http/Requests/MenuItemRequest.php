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
            '*.*.locale' => 'sometimes|required|max:15',
            '*.*.title' => 'sometimes|required|max:255',
            '*.*.type' => 'sometimes|required|max:255',
            '*.*.url' => 'nullable',
            '*.*.page_id' => 'nullable',
            '*.*.post_id' => 'nullable',
            '*.*.category_id' => 'nullable',
            '*.*.menu_id' => 'sometimes|required',
        ];
    }

    public function messages()
    {
        return [
            'en.*.locale.required' => 'Locale (English) is required',
            'en.*.title.required' => 'Title (English) is required',
            'en.*.type.required' => 'Type (English) is required',
            'sv.*.locale.required' => 'Locale (Swedish) is required',
            'sv.*.title.required' => 'Title (Swedish) is required',
            'sv.*.type.required' => 'Type (Swedish) is required',
            'es.*.locale.required' => 'Locale (Spanish) is required',
            'es.*.title.required' => 'Title (Spanish) is required',
            'es.*.type.required' => 'Type (Spanish) is required',
            'de.*.locale.required' => 'Locale (German) is required',
            'de.*.title.required' => 'Title (German) is required',
            'de.*.type.required' => 'Type (German) is required',
        ];
    }
}
