<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class TranslationService
{
    public static $localeKey = 'locale';

    public static function getDefaultLocale(): string
    {
        return config('app.fallback_locale');
    }

    public static function getLocaleOptions(): array
    {
        return Language::active()
            ->get(['code', 'name'])
            ->map(function ($language) {
                return [
                    'id' => $language->code,
                    'name' => $language->name,
                ];
            })
            ->all();
    }

    public static function getLocales(): array
    {
        return array_map(
            function ($option) { return $option['id']; },
            self::getLocaleOptions()
        );
    }

    public static function getLanguageFromLocale($locale): ?string
    {
        $firstOption = collect(self::getLocaleOptions())
            ->firstWhere('id', $locale);
        return empty($firstOption) ? null : $firstOption['name'];
    }

    public static function hasLanguage(): bool
    {
        return session()->has(self::$localeKey);
    }

    public static function currentLanguage(): string
    {
        return session(self::$localeKey) ?? self::getDefaultLocale();
    }

    public static function setLanguage(string $locale): void
    {
        session()->put(self::$localeKey, $locale);
    }

    public static function setLanguageAndAppLocale(string $locale): void
    {
        self::setLanguage($locale);
        app()->setLocale($locale);
    }

    /**
     * @return array, example result = [
     *   'en.name' => 'Name (English)',
     * ]
     */
    public static function getCustomAttributes(
        array $locales,
        array $attributes,
        string $prefix = 'translations.'
    ): array {
        $translatedAttributes = [];
        foreach ($locales as $locale) {
            foreach ($attributes as $attribute) {
                $attributeKey = $prefix.$locale.'.'.$attribute;

                $translationKey = 'validation.attributes.'.$attribute;

                $attributeName = $attribute;

                if (Lang::has($translationKey)) {
                    $attributeName = trans('validation.attributes.'.$attribute);
                }

                $translatedAttributes[$attributeKey] = (
                    Str::title($attributeName).
                    " (".TranslationService::getLanguageFromLocale($locale).")"
                );
            }
        }
        return $translatedAttributes;
    }
}
