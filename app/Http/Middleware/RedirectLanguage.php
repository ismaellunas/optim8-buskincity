<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\{
    LanguageService,
    TranslationService
};
use Illuminate\Http\Request;

class RedirectLanguage
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
        $redirect = $this->setRedirect();

        $path = $this->setPath($request->path());

        if ($path != $redirect) {
            return redirect($redirect);
        } else {
            return $next($request);
        }
    }

    private function setRedirect(): string {
        $redirect = "/";
        $originLanguage = app(LanguageService::class)->getOriginLanguageFromCookie();
        $defaultLanguage = TranslationService::getDefaultLocale();
        $locales = TranslationService::getLocales();

        if ($defaultLanguage != $originLanguage && in_array($originLanguage, $locales)) {
            $redirect = "/".$originLanguage;
        }

        return $redirect;
    }

    private function setPath(string $path): string
    {
        return $path == "/" ? "/" : "/".$path;
    }
}
