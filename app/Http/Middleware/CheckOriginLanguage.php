<?php

namespace App\Http\Middleware;

use App\Entities\LifetimeCookie;
use App\Services\LanguageService;
use Closure;
use Illuminate\Http\Request;

class CheckOriginLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $languageService = app(LanguageService::class);

        $currentLocale = currentLocale();
        $originLanguage = $languageService->getOriginLanguage();

        if ($originLanguage !== $currentLocale) {
            $languageService->setOriginLanguage($currentLocale);
        }

        return $next($request);
    }
}
