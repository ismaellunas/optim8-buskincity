<?php

namespace App\Http\Controllers;

use App\Services\TranslationService as TranslationSv;

class ChangeLanguageController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke($newLocale)
    {
        TranslationSv::setLanguageAndAppLocale($newLocale);

        $url = url()->previous();

        if (!empty($url)) {
            $route = app('router')->getRoutes($url)->match(app('request')->create($url));
            $prevRouteName = $route->getName();

            if (empty($prevRouteName)) {
                return redirect('/');
            }

            $prevParams = $route->parameters;
            $prevParams['locale'] = $newLocale;

            return redirect()->route($prevRouteName, $prevParams);
        } else {
            return redirect('/');
        }
    }
}
