<?php

namespace App\Services;

use App\Entities\Caches\SettingCache;
use App\Facades\Localization;
use App\Services\LanguageService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class TranslationService
{
    public static $localeKey = 'locale';

    private static $defaultLocale;

    private $localeOptions;

    public static function getDefaultLocale(): string
    {
        if (is_null(self::$defaultLocale)) {

            $key = config('constants.setting_cache.default_locale');

            try {
                self::$defaultLocale = app(SettingCache::class)->remember(
                    $key,
                    function () {
                        return app(LanguageService::class)->getDefault()->code ?? config('app.fallback_locale');
                    },
                    config('app.fallback_locale')
                );
            } catch (QueryException $e) {
                if (in_array($e->getCode(), ["42P01", "2002"])) {
                    return config('app.fallback_locale');
                }
            }
        }

        return self::$defaultLocale;
    }

    public function getLocaleOptions(): array
    {
        if (is_null($this->localeOptions)) {
            $this->localeOptions = app(LanguageService::class)
                ->getSupportedLanguages()
                ->map(function ($language) {
                    return [
                        'id' => $language->code,
                        'name' => $language->name,
                    ];
                })
                ->sortBy(function ($value) {
                    return $value['id'] !== self::getDefaultLocale();
                })
                ->values()
                ->all();
        }

        return $this->localeOptions;
    }

    public function getLocales(): array
    {
        return array_map(
            function ($option) { return $option['id']; },
            $this->getLocaleOptions()
        );
    }

    public function getLanguageFromLocale($locale): ?string
    {
        $firstOption = collect($this->getLocaleOptions())
            ->firstWhere('id', $locale);
        return empty($firstOption) ? null : $firstOption['name'];
    }

    public static function hasLanguage(): bool
    {
        return session()->has(self::$localeKey);
    }

    public static function currentLanguage(): string
    {
        return Localization::getCurrentLocale() ?? self::getDefaultLocale();
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
                    " (".app(TranslationService::class)->getLanguageFromLocale($locale).")"
                );
            }
        }
        return $translatedAttributes;
    }

    public function isSupportedLocale(string $locale): bool
    {
        return in_array($locale, $this->getLocales());
    }
}
