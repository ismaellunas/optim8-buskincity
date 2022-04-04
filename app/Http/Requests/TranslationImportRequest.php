<?php

namespace App\Http\Requests;

class TranslationImportRequest extends BaseFormRequest
{
    protected $errorBag = 'translationImport';

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

    protected function customAttributes(): array
    {
        return [
            'file' => __('File'),
        ];
    }
}
