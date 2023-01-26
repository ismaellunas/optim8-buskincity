<?php

use App\Services\TranslationService;

if (! function_exists('defaultLocale')) {
    function defaultLocale()
    {
        return TranslationService::getDefaultLocale();
    }
}

if (! function_exists('currentLocale')) {
    function currentLocale()
    {
        return TranslationService::currentLanguage();
    }
}
