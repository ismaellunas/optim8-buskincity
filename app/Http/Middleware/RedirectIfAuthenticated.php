<?php

namespace App\Http\Middleware;

use App\Services\LoginService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if (
                    Auth::user()->can('system.dashboard')
                    && $request->routeIs('admin.login')
                    && LoginService::isAdminHomeUrl()
                ) {
                    return redirect(config('fortify.admin_home'));
                }

                if (
                    $request->routeIs('login')
                    && LoginService::isUserHomeUrl()
                ) {
                    return redirect(config('fortify.home'));
                }
            }
        }

        return $next($request);
    }
}
