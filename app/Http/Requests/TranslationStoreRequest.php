<?php

namespace App\Http\Requests;

use App\Services\{
    TranslationService,
    TranslationManagerService
};
use Illuminate\Foundation\Http\FormRequest;
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
                'max: 1024',
                Rule::unique('translations')
                    ->where(function ($query) use ($group, $key) {
                        return $query->where('group', $group)
                            ->where('key', $key);
                    })
            ],
            'value' => ['array'],
            'value.' . $this->referenceLocale => ['required'],
            'value.*' => [
                'max: 1024',
            ]
        ];
    }

    protected function customAttributes(): array
    {
        $attributes = [];

        foreach (TranslationService::getLocaleOptions() as $locale) {
            $attributes['value.' . $locale['id']] = $locale['name'] . ' value';
        }

        return $attributes;
    }
}
