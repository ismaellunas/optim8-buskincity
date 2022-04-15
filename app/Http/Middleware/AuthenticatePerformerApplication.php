<?php

namespace App\Http\Middleware;

use App\Models\PerformerApplication;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePerformerApplication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (
            $user->roles->isNotEmpty()
            || PerformerApplication::where('applicant_id', $user->id)->exists()
        ) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
