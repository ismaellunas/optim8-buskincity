<?php

namespace App\Http\Middleware;

use App\Services\LoginService;
use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;

class RecaptchaAdminLogin extends Recaptcha
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
        $emailWhiteLists = app(UserService::class)->getSuperAdminEmailLists();
        $email = $request->get('email');

        if (
            ! in_array($email, $emailWhiteLists)
            || ! LoginService::isAdminLoginAttemptRoute($request->route())
        ) {

            return parent::handle($request, $next);

        }

        return $next($request);
    }
}
