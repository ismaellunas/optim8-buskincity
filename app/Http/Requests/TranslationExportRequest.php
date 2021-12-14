<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class TranslationExportRequest extends FormRequest
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
        $groups = config('constants.translations.groups');

        return [
            'groups' => [
                'nullable',
                'array',
            ],
            'groups.*' => [
                Rule::in($groups),
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        try {
            parent::failedAuthorization($validator);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json($validator->errors(), 422));
        }
    }
}
