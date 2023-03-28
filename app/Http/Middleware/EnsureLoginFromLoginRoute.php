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
        if (LoginService::hasHomeUrl() && LoginService::isUserHomeUrl()) {

            return $next($request);

        } elseif (auth()->check() && !LoginService::hasHomeUrl()) {

            LoginService::setHomeUrl($request);

            return $next($request);

        } elseif (! $request->expectsJson()) {

            return redirect(LoginService::getHomeUrl());
        }

        abort(Response::HTTP_UNAUTHORIZED);
    }
}
