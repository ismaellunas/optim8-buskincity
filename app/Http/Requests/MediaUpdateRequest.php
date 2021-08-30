<?php

namespace App\Http\Requests;

use App\Rules\AlphaNumericDash;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Foundation\Http\FormRequest;

class MediaUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = RuleFactory::make([
            'translations.%alt%' => 'sometimes|nullable|string|max:255',
        ]);

        return array_merge($rules, [
            'file_name' => [
                'required',
                new AlphaNumericDash(),
                'max:255'
            ],
        ]);
    }
}
