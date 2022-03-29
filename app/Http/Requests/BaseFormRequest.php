<?php

namespace App\Http\Requests;

use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseFormRequest extends FormRequest
{
    final public function attributes(): array
    {
        return array_merge(
            $this->translatedAttributes(),
            $this->customAttributes()
        );
    }

    final protected function translatedAttributes(): array
    {
        $keys = array_keys($this->rules());
        $defaultLocale = TranslationService::getDefaultLocale();

        $attributes = [];
        $translations = (new Translation())
            ->loadTranslations($defaultLocale, 'validation');

        foreach ($keys as $key) {
            if (isset($translations['attributes.' . $key])) {
                $attributes[$key] = $translations['attributes.' . $key];
            }
        }

        return $attributes;
    }

    protected function customAttributes(): array
    {
        return [];
    }
}
