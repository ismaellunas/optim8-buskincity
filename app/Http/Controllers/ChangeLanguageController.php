<?php

namespace App\Http\Controllers;

use App\Entities\LifetimeCookie;
use App\Helpers\Url;
use App\Services\TranslationService as TranslationSv;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        app(LifetimeCookie::class)->set(
            'origin_language',
            $newLocale != '' ? $newLocale : $defaultLocale
        );

        if (!empty($url)) {
            $removedLocaleUrl = $this->removeLocaleFromUrl($url);

            $prevRouteName = null;
            $prevParams = [];

            try {
                $route = app('router')
                    ->getRoutes($removedLocaleUrl)
                    ->match(app('request')->create($removedLocaleUrl));

            } catch (NotFoundHttpException $e){
                $route = app('router')
                    ->getRoutes($url)
                    ->match(app('request')->create($url));
            }

            $prevParams = $route->parameters;

            $prevRouteName = $route->getName();

            if (empty($prevRouteName)) {
                return redirect('/'.$newLocale);
            }

            $url = LaravelLocalization::getURLFromRouteNameTranslated(
                $newLocale,
                $prevRouteName,
                $prevParams
            );

            return redirect($url);
        }

        return redirect('/'.$newLocale);
    }

    private function appendLocaleToUrl(
        ?string $locale,
        string $url
    ): string {
        $uriPath = Url::getPath($url);

        if ($locale == "") {
            return $url;
        }

        return config('app.url')."/".$locale.$uriPath;
    }

    private function removeLocaleFromUrl(string $url): string
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
