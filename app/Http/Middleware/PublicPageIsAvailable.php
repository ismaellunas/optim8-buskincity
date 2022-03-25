<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        if (
            $user->can('public_page.'.$permission)
            && $this->sameName($user->fullName, $request->route('firstname_lastname'))
        ) {
            return $next($request);
        }

        abort(404);
    }

    private function sameName(string $fullName, string $routeName): bool
    {
        $fullName = Str::of($fullName)->ascii()->replace(' ', '-')->lower();
        $routeName = Str::of($routeName)->ascii()->replace(' ', '-')->lower();

        return ($fullName == $routeName);
    }
}
