<?php

namespace App\Entities;

use App\Services\{
    LanguageService,
    TranslationService
};
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Mcamara\LaravelLocalization\LaravelLocalization;
use Mcamara\LaravelLocalization\Exceptions\{
    SupportedLocalesNotDefined,
    UnsupportedLocaleException
};

class Localization extends LaravelLocalization
{
    public function __construct()
    {
        try {
            parent::__construct();
        } catch (\Throwable $e) {
            $this->app = app();

            $this->configRepository = $this->app['config'];
            $this->view = $this->app['view'];
            $this->translator = $this->app['translator'];
            $this->router = $this->app['router'];
            $this->request = $this->app['request'];
            $this->url = $this->app['url'];
        }

        try {

            $this->defaultLocale = TranslationService::getDefaultLocale();

        } catch (QueryException $e) {

            if ($e->getCode() == '42P01') {
                $this->defaultLocale = config('app.fallback_locale');
            } else {
                throw $e;
            }
        }

        $supportedLocales = $this->getSupportedLocales();
        if (empty($supportedLocales[$this->defaultLocale])) {
            throw new UnsupportedLocaleException('Laravel default locale is not in the supportedLocales array.');
        }
    }

    // Override from Mcamara\LaravelLocalization\LaravelLocalization
    public function getSupportedLocales()
    {
        if (!empty($this->supportedLocales)) {
            return $this->supportedLocales;
        }

        $languageService = app(LanguageService::class);

        try {
            $supportedLanguages = $languageService->getSupportedLanguages();

            if (! $supportedLanguages->isEmpty()) {
                $locales = $this->formatSupportedLocale($supportedLanguages);
            } else {
                $locales = $this->getDefaultSupportedLocales();
            }

        } catch (QueryException $e) {

            if ($e->getCode() == '42P01') {
                $locales = $this->getDefaultSupportedLocales();
            } else {
                throw $e;
            }
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
