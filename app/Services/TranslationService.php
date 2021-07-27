<?php

namespace App\Services;

class TranslationService
{
    public static function getDefaultLocale(): string
    {
        return config('app.locale');
    }

    public static function getLocaleOptions(): array
    {
        return [
            [
                'id' => 'en',
                'name' => 'English',
            ],
            [
                'id' => 'sv',
                'name' => 'Swedish',
            ],
            [
                'id' => 'es',
                'name' => 'Spanish',
            ],
            [
                'id' => 'de',
                'name' => 'German',
            ],
        ];
    }

    public static function getLocales(): array
    {
        return array_map(
            function ($option) { return $option['id']; },
            self::getLocaleOptions()
        );
    }

    public static function getLanguageFromLocale($locale): null|string
    {
        $firstOption = collect(self::getLocaleOptions())
            ->firstWhere('id', $locale);
        return empty($firstOption) ? null : $firstOption['name'];
    }
}
