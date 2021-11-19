<?php

namespace App\Http\Controllers;

use App\Services\TranslationService as TranslationSv;
use Illuminate\Support\Str;
use LaravelLocalization;

class ChangeLanguageController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke($newLocale)
    {
        $url = url()->previous();
        $defaultLocale = TranslationSv::getDefaultLocale();

        if ($newLocale === $defaultLocale) {
            $newLocale = "";
        }

        if (!empty($url)) {
            $url = $this->removeLocaleFromUrl($url);
            $route = app('router')->getRoutes($url)->match(app('request')->create($url));
            $prevRouteName = $route->getName();

            if (empty($prevRouteName)) {
                return redirect('/'.$newLocale);
            }

            $prevParams = $route->parameters;
            $url = $this->appendLocaleToUrl(
                $newLocale,
                route($prevRouteName, $prevParams)
            );

            return redirect($url);
        }

        return redirect('/'.$newLocale);
    }

    private function appendLocaleToUrl(
        ?string $locale,
        string $url
    ):string {
        $segments = explode(config('app.url'), $url);

        if ($locale == "") {
            return $url;
        }

        return config('app.url')."/".$locale.$segments[1];
    }

    private function removeLocaleFromUrl(string $url)
    {
        $uriPath = parse_url($url, PHP_URL_PATH);
        $uriSegments = explode('/', $uriPath);
        $locales = LaravelLocalization::getSupportedLanguagesKeys();

        if (Str::startsWith($uriSegments[1], $locales)) {
            $segments = explode(config('app.url'), $url);
            $segments[1] = Str::replaceFirst('/'.$uriSegments[1], '', $segments[1]);

            $url = config('app.url').$segments[1];
        }

        return $url;
    }
}
