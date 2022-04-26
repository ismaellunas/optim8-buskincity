<?php

namespace App\Actions\Fortify;

use App\Services\LoginService;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession as FortifyPrepareAuthenticatedSession;

class PrepareAuthenticatedSession extends FortifyPrepareAuthenticatedSession
{
    /** @override */
    public function handle($request, $next)
    {
        if ($request->routeIs('admin.*')) {
            LoginService::setAdminHomeUrl();
        } else {
            LoginService::setUserHomeUrl();
        }

        return parent::handle($request, $next);
    }
}
