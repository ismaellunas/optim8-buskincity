<?php

namespace App\Http\Middleware;

use App\Services\LoginService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLoginFromAdminLoginRoute
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
        $user = $request->user();

        if ($user?->canAccessAdminPanel()) {
            LoginService::applyIdentity($user);

            return $next($request);
        }

        if (! $request->expectsJson()) {
            return redirect(LoginService::redirectPathFor($user));
        }

        abort(Response::HTTP_UNAUTHORIZED);
    }
}
