<?php

namespace App\Http\Requests;

use Astrotomic\Translatable\Validation\RuleFactory;

class CategoryRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return RuleFactory::make([
            '%name%' => 'sometimes|required',
        ]);
    }
}
