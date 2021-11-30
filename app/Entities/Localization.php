<?php

namespace App\Entities;

use App\Services\{
    LanguageService,
    TranslationService
};
use Illuminate\Support\Collection;
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

        $languageService = app(LanguageService::class);
        $supportedLanguages = $languageService->getSupportedLanguages();

        if (! $supportedLanguages->isEmpty()) {
            $locales = $this->formatSupportedLocale($languageService->getSupportedLanguages());
        } else {
            $locales = $this->getDefaultSupportedLocales();
        }

        if (empty($locales) || !is_array($locales)) {
            throw new SupportedLocalesNotDefined();
        }

        $this->supportedLocales = $locales;

        return $locales;
    }

    private function formatSupportedLocale(Collection $locales): array
    {
        return $locales
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
    }

    private function getDefaultSupportedLocales(): array
    {
        return [
            config('app.fallback_locale') => [
                'code' => config('app.fallback_locale'),
                'name' => config('app.fallback_locale'),
                'script' => '',
                'native' => '',
                'regional' => config('app.fallback_locale'),
            ]
        ];
    }
}
