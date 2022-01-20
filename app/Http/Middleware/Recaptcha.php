<?php

namespace App\Http\Middleware;

use Closure;
use ReCaptcha\ReCaptcha as GoogleRecaptcha;

class Recaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        $response = (new GoogleRecaptcha(env('RECAPTCHA_SECRET_KEY')))
            ->verify($request->input('g-recaptcha-response'), $request->ip());

        if (!$response->isSuccess()) {
            return redirect()->back()->with('failed', 'Recaptcha failed. Please try again.');
        }

        return $next($request);
    }
}
