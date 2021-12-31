<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;

class CheckSuspended
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
        if (auth()->check() && (auth()->user()->is_suspended)) {

            app(StatefulGuard::class)->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            $loginRoute = 'login';

            if ($request->routeIs('admin.*')) {
                $loginRoute = 'admin.login';
            }

            return redirect()
                ->route($loginRoute)
                ->withErrors(__('Your Account is suspended, please contact the support.'));
        }

        return $next($request);
    }
}
