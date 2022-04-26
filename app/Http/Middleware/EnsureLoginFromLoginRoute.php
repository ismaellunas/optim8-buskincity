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
        if (! $request->expectsJson() && LoginService::hasHomeUrl()) {

            if (LoginService::isUserHomeUrl()) {
                return $next($request);
            } else {
                return redirect(LoginService::getHomeUrl());
            }
        }

        abort(Response::HTTP_UNAUTHORIZED);
    }
}
