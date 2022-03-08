<?php

namespace App\Entities\Forms\Fields;

use App\Services\TranslationService;
use Illuminate\Support\Str;

abstract class TranslatableField extends BaseField
{
    public $originLanguage;
    public $translated;

    private $defaultLocale;
    private $locales;

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->defaultLocale = TranslationService::getDefaultLocale();
        $this->locales = TranslationService::getLocales();
    }

    protected function setPropertiesBasedOnData()
    {
        parent::setPropertiesBasedOnData();

        $this->translated = $this->data['translated'] ?? false;
    }

    protected function schema(): array
    {
        $schema = parent::schema();

        return array_merge($schema, [
            'is_translated' => $this->translated,
        ]);
    }

    public function setOriginLanguage(?string $languageCode = null): void
    {
        $this->originLanguage = $languageCode ?? $this->defaultLocale;
    }

    public function translatedValidationRules(): array
    {
        $rules = [];

        if (!in_array($this->originLanguage, $this->locales)){
            $this->originLanguage = $this->defaultLocale;
        }

        $providedRules = collect($this->validation['rules'] ?? []);

        foreach ($this->locales as $locale) {
            if ($this->originLanguage != $locale) {
                $rules[$this->name.".".$locale] = $providedRules->filter(
                        function ($value) {
                            return $value != 'required';
                        }
                    )
                    ->values()
                    ->all();
            } else {
                $rules[$this->name.".".$locale] = $providedRules->all();
            }
        }

        $this->adjustNullableRule($rules);

        return $rules;
    }

    public function translatedValidationAttributes(array $attributes): array
    {
        $translatedAttributes = [];
        foreach ($this->locales as $locale) {
            foreach ($attributes as $attribute) {
                $attributeKey = $attribute.'.'.$locale;

                $attributeName = Str::replace("_", " ", $attribute);

                $translatedAttributes[$attributeKey] = (
                    Str::title($attributeName).
                    " (".TranslationService::getLanguageFromLocale($locale).")"
                );
            }
        }
        return $translatedAttributes;
    }
}