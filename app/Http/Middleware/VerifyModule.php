<?php

namespace App\Http\Middleware;

use App\Services\ModuleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VerifyModule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$modules)
    {
        return $this->isAvailableByModules($modules)
            ? $next($request)
            : abort(403, 'Module is disabled');
    }

    protected function isAvailableByModules(array $modules): bool
    {
        $operator = 'OR';

        if (in_array(Str::upper(last($modules)), ['OR', 'AND'])) {
            $operator = Str::upper(array_pop($modules));
        }

        return call_user_func_array(
            [app(ModuleService::class), 'isAvailableByModules'],
            [$modules, $operator]
        );
    }
}
