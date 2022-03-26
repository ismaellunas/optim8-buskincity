<?php

namespace App\Http\Requests;

use App\Services\TranslationManagerService;
use Illuminate\Foundation\Http\FormRequest;

class TranslationStoreRequest extends FormRequest
{
    private $translationManagerService;
    private $referenceLocale;

    public function __construct(
        TranslationManagerService $translationManagerService
    ) {
        $this->translationManagerService = $translationManagerService;

        $this->referenceLocale = $this->translationManagerService->getReferenceLocale();
    }

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
        return [
            'key' => [
                'required',
                'string',
                'unique:translations,key'
            ],
            'value' => ['array'],
            'value.' . $this->referenceLocale => ['required']
        ];
    }

    public function attributes(): array
    {
        return [
            'value.' . $this->referenceLocale => 'English Value',
        ];
    }
}
