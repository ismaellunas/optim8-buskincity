<?php

namespace App\Http\Middleware;

use Closure;
use App\Entities\LifetimeCookie;
use App\Services\{
    LanguageService,
    LoginService,
    TranslationService,
};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RedirectOriginLanguage
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
        $path = $this->setPath($request->path());
        $redirect = $this->setRedirect($path);

        if ($path != $redirect && !LoginService::hasHomeUrl()) {
            return redirect($redirect);
        } else {
            return $next($request);
        }
    }

    private function setRedirect(string $path): string {
        $originLanguage = $this->setOriginLanguage();
        $locales = app(TranslationService::class)->getLocales();

        $uriSegments = explode('/', $path);

        if (count($uriSegments) > 1 && in_array($uriSegments[1], $locales)) {
            $path = Str::replaceFirst(
                '/'.$uriSegments[1],
                $originLanguage ? '/'.$originLanguage : $originLanguage,
                $path
            );
        } else {
            if ($originLanguage != "") {
                $path = '/'.$originLanguage.$path;
            }
        }

        return $path;
    }

    private function setOriginLanguage(): string
    {
        $originLanguage = app(LanguageService::class)->getOriginLanguageFromCookie();
        $defaultLanguage = defaultLocale();

        if ($originLanguage == $defaultLanguage) {
            $originLanguage = "";
        }

        return $originLanguage;
    }

    private function setPath(string $path): string
    {
        return $path == "/" ? "/" : "/".$path;
    }
}
