<?php

namespace App\Entities;

use App\Services\{
    LanguageService,
    TranslationService
};
use Mcamara\LaravelLocalization\LaravelLocalization;

class Localization extends LaravelLocalization
{
    public function __construct()
    {
        parent::__construct();
        $this->defaultLocale = TranslationService::getDefaultLocale();
    }

    // Override from Mcamara\LaravelLocalization\LaravelLocalization
    public function getSupportedLocales()
    {
        if (!empty($this->supportedLocales)) {
            return $this->supportedLocales;
        }

        $languageService = new LanguageService();
        $locales = $this->setSupportedLocaleFormat($languageService->getSupportedLanguages());

        $this->supportedLocales = $locales;

        return $locales;
    }

    private function setSupportedLocaleFormat($locales): array
    {
        $locales = collect($locales)
            ->map(function ($language) {
                return [
                    'code' => $language->code,
                    'name' => $language->name,
                    'script' => '',
                    'native' => '',
                    'regional' => $language->locale,
                ];
            })
            ->keyBy('code')
            ->toArray();

        return $locales;
    }
}
