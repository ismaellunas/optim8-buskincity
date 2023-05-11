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

if (! function_exists('putSession')) {
    function putSession(string $key, mixed $value): void
    {
        session()->put($key, $value);
    }
}

if (! function_exists('getSession')) {
    function getSession(string $key, mixed $defaultValue = null): mixed
    {
        $value = session()->get($key);

        if (! $value && $defaultValue) {
            putSession($key, $defaultValue);

            $value = $defaultValue;
        }

        return $value;
    }
}

if (! function_exists('forgetSession')) {
    function forgetSession(string $key): void
    {
        session()->forget($key);
    }
}