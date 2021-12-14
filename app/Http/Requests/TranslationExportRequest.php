<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TranslationExportRequest extends TranslationManagerRequest
{
    protected function failedValidation(Validator $validator)
    {
        try {
            parent::failedAuthorization($validator);
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json($validator->errors(), 422));
        }
    }
}
