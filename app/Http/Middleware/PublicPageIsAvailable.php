<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PublicPageIsAvailable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->route('user');

        if ($user->can('public_page.'.$permission)) {
            return $next($request);
        }

        abort(404);
    }
}
