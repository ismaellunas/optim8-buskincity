<?php

namespace App\Entities;

use App\Services\LanguageService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
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

        if (App::environment('testing')) {

            $this->defaultLocale = config('app.fallback_locale');

            $supportedLocales = parent::getSupportedLocales();

        } else {

            try {

                $this->defaultLocale = defaultLocale();

            } catch (QueryException $e) {

                if ($e->getCode() == '42P01') {
                    $this->defaultLocale = config('app.fallback_locale');
                } else {
                    throw $e;
                }

            } catch (\Predis\Connection\ConnectionException $e) {

                $this->defaultLocale = $this->configRepository->get('app.fallback_locale');
            }

            try {

                $supportedLocales = $this->getSupportedLocales();

            } catch (\Illuminate\Database\QueryException $e) {

                $supportedLocales = parent::getSupportedLocales();
            }
        }

        if (
            !app()->runningInConsole()
            && empty($supportedLocales[$this->defaultLocale])
        ) {
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

    // Override from Mcamara\LaravelLocalization\LaravelLocalization
    public function transRoute($routeName)
    {
        $transRoute = parent::transRoute($routeName);

        if ($transRoute == $routeName) {
            $transRoute = $this->translator->get(
                $routeName,
                [],
                defaultLocale()
            );
        }

        return $transRoute;
    }

    // Override from Mcamara\LaravelLocalization\LaravelLocalization
    public function getURLFromRouteNameTranslated($locale, $transKeyName, $attributes = [], $forceDefaultLocation = false)
    {
        try {
            if (!$this->checkLocaleInSupportedLocales($locale)) {
                throw new UnsupportedLocaleException('Locale \''.$locale.'\' is not in the list of supported locales.');
            }

            if (!\is_string($locale)) {
                $locale = $this->getDefaultLocale();
            }

            $route = '';

            if ($forceDefaultLocation || !($locale === $this->defaultLocale && $this->hideDefaultLocaleInURL())) {
                $route = '/'.$locale;
            }
            if (\is_string($locale)) {
                $translation = $this->translator->get($transKeyName, [], $locale);

                if ($translation == $transKeyName) {
                    $translation = $this->translator->get(
                        $transKeyName,
                        [],
                        defaultLocale()
                    );
                }

                $route .= '/'.$translation;

                $route = $this->substituteAttributesInRoute($attributes, $route, $locale);
            }

            if (empty($route)) {
                // This locale does not have any key for this route name
                return false;
            }

            return rtrim($this->createUrlFromUri($route), '/');
        } catch (\Throwable $th) {
            return parent::getURLFromRouteNameTranslated($locale, $transKeyName, $attributes, $forceDefaultLocation);
        }
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
