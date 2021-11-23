<?php

namespace App\Http\Controllers;

use App\Helpers\Url;
use App\Services\TranslationService as TranslationSv;
use Illuminate\Support\Str;

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
        $uriPath = Url::getPath($url);

        if ($locale == "") {
            return $url;
        }

        return config('app.url')."/".$locale.$uriPath;
    }

    private function removeLocaleFromUrl(string $url)
    {
        $uriPath = Url::getPath($url);
        $uriSegments = explode('/', $uriPath);
        $locales = TranslationSv::getLocales();

        if (in_array($uriSegments[1], $locales)) {
            $uriPath = Str::replaceFirst('/'.$uriSegments[1], '', $uriPath);
        }

        return config('app.url').$uriPath;
    }
}
