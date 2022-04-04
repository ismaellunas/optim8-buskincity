<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use App\Services\TranslationService;
use Astrotomic\Translatable\Validation\RuleFactory;
use Illuminate\Validation\Rule;

class PageRequest extends BaseFormRequest
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
        $inputs = $this->findInputByLocale();

        return RuleFactory::make([
            '%title%' => 'sometimes|string',
            '%slug%' => [
                'sometimes',
                'required',
                'alpha_dash',
                Rule::unique('page_translations', 'slug')
                    ->where(function ($query) use ($inputs) {
                        return $query->where('slug', $inputs['slug'])
                            ->where('locale', $inputs['locale']);
                    })
                    ->ignore($inputs['id'])
            ],
        ]);
    }

    protected function customAttributes(): array
    {
        $translatedAttributes = [];
        $locales = array_keys($this->all());
        $attributes = ['title', 'slug'];

        foreach ($locales as $locale) {
            foreach ($attributes as $attribute) {
                $attributeKey = $locale.'.'.$attribute;
                $translatedAttributes[$attributeKey] = (
                    Str::title($attribute).
                    " (".TranslationService::getLanguageFromLocale($locale).")"
                );
            }
        }

        return $translatedAttributes;
    }

    private function findInputByLocale(): array
    {
        $inputs = [];
        $locales = array_keys($this->all());

        foreach ($locales as $locale) {
            if (count($this[$locale]) > 0) {
                $inputs = $this[$locale];
                $inputs['locale'] = $locale;
            }
        }

        return $inputs;
    }
}
