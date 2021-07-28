<?php

namespace App\Http\Middleware;

use App\Services\TranslationService as TranslationSv;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (TranslationSv::hasLanguage()) {
            TranslationSv::setLanguageAndAppLocale(TranslationSv::currentLanguage());
        }

        URL::defaults([TranslationSv::$localeKey => TranslationSv::currentLanguage()]);

        return $next($request);
    }
}
