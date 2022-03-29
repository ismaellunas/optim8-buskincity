<?php

namespace App\Http\Requests;

use App\Services\TranslationManagerService;
use Illuminate\Validation\Rule;

class TranslationStoreRequest extends BaseFormRequest
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
        $group = $this->group;
        $key = $this->key;

        return [
            'key' => [
                'required',
                'string',
                Rule::unique('translations')
                    ->where(function ($query) use ($group, $key) {
                        return $query->where('group', $group)
                            ->where('key', $key);
                    })
            ],
            'value' => ['array'],
            'value.' . $this->referenceLocale => ['required']
        ];
    }

    protected function customAttributes(): array
    {
        return [
            'value.' . $this->referenceLocale => 'English Value',
        ];
    }
}
