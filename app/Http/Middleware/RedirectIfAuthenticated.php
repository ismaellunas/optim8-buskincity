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
                $user = Auth::user();
                LoginService::applyIdentity($user);

                if ($request->routeIs('admin.login') || $request->routeIs('login')) {
                    return redirect(LoginService::redirectPathFor($user));
                }
            }
        }

        return $next($request);
    }
}
