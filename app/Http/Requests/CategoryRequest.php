<?php

namespace App\Http\Requests;

use App\Rules\AlphaNumericDash;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
