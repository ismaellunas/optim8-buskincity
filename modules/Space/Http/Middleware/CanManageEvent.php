<?php

namespace Modules\Space\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CanManageEvent
{
    public function handle(Request $request, Closure $next)
    {
        $space = $request->route('space');

        if (! auth()->user()->can('update', $space)) {
            abort(403);
        }

        return $next($request);
    }
}
