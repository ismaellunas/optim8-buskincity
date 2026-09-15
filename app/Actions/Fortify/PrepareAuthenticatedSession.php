<?php

namespace App\Actions\Fortify;

use App\Services\LoginService;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession as FortifyPrepareAuthenticatedSession;

class PrepareAuthenticatedSession extends FortifyPrepareAuthenticatedSession
{
    /** @override */
    public function handle($request, $next)
    {
        $request->session()->regenerate();
        LoginService::setHomeUrl($request);

        return $next($request);
    }
}
