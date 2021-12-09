<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TranslationImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:'.config('constants.one_megabyte') * 5,
                'mimes:'.implode(',', config('constants.extensions.import')),
            ],
        ];
    }
}
