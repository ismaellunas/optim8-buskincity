<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfModuleIsDisabled extends VerifyModule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$modules)
    {
        return $this->isAvailableByModules($modules)
            ? $next($request)
            : redirect('homepage');
    }
}
