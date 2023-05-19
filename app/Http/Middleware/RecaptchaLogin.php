<?php

namespace App\Http\Middleware;

use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;

class RecaptchaLogin extends Recaptcha
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
        $redirectResponse = parent::handle($request, $next);

        $emailWhiteLists = (new UserService())->getSuperAdminEmailLists();
        $email = $request->get('email');

        if (in_array($email, $emailWhiteLists)) {
            return $next($request);
        }

        return $redirectResponse;
    }
}
