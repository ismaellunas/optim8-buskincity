<?php

namespace App\Services;

class TranslationService
{
    public static function getDefaultLocaleCode(): string
    {
        return config('app.locale');
    }

    public static function getLocaleAndCodeOptions(): array
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
}
