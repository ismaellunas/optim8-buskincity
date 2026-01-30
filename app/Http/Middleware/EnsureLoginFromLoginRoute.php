<?php

namespace App\Http\Middleware;

use App\Services\LoginService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLoginFromLoginRoute
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
        // Allow authenticated JSON/Inertia requests to proceed
        if (auth()->check() && ($request->expectsJson() || $request->header('X-Inertia'))) {
            if (!LoginService::hasHomeUrl()) {
                LoginService::setHomeUrl($request);
            }
            return $next($request);
        }

        if (LoginService::hasHomeUrl() && LoginService::isUserHomeUrl()) {
            return $next($request);

        } elseif (auth()->check() && !LoginService::hasHomeUrl()) {
            LoginService::setHomeUrl($request);

            return $next($request);

        } elseif (! $request->expectsJson() && ! $request->header('X-Inertia')) {
            return redirect(LoginService::getHomeUrl());
        }

        abort(Response::HTTP_UNAUTHORIZED);
    }
}
