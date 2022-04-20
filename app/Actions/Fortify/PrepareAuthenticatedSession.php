<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Actions\PrepareAuthenticatedSession as FortifyPrepareAuthenticatedSession;

class PrepareAuthenticatedSession extends FortifyPrepareAuthenticatedSession
{
    /** @override */
    public function handle($request, $next)
    {
        if (! $request->routeIs('admin.*')) {
            $request->session()->put('login_from_login_route', true);
        }

        return parent::handle($request, $next);
    }
}
