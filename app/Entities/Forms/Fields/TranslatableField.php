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
        $this->locales = app(TranslationService::class)->getLocales();
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

    public function validationRules(): array
    {
        if ($this->translated) {
            return $this->translatedValidationRules();
        }

        return parent::validationRules();
    }

    public function validationAttributes(array $inputs = []): array
    {
        $attributes = parent::validationAttributes($inputs);

        if ($this->translated) {
            $attributes = $this->translatedValidationAttributes(
                array_keys($attributes)
            );
        }

        return $attributes;
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
                        function ($value, $key) {
                            return $key != 'required';
                        }
                    )
                    ->all();
            } else {
                $rules[$this->name.".".$locale] = $providedRules->all();
            }
        }

        $this->transformToFlatten($rules);
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
                    " (".app(TranslationService::class)->getLanguageFromLocale($locale).")"
                );
            }
        }
        return $translatedAttributes;
    }
}