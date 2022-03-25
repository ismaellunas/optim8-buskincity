<?php

namespace App\Http\Requests;

use App\Services\TranslationManagerService;
use Illuminate\Foundation\Http\FormRequest;

class TranslationStoreRequest extends FormRequest
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
        $translationManagerService = new TranslationManagerService();
        $referenceLocale = $translationManagerService->getReferenceLocale();

        return [
            'key' => [
                'required',
                'string',
                'unique:translations,key'
            ],
            'value' => ['array'],
            'value.' . $referenceLocale => ['required']
        ];
    }
}
