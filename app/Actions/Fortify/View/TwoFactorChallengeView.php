<?php

namespace App\Actions\Fortify\View;

use App\Helpers\Url;
use App\Services\LoginService;
use Inertia\Inertia;

class TwoFactorChallengeView
{
    public function __invoke()
    {
        $url = url()->current();
        $route = Url::getRoute($url);

        if (LoginService::isAdminTwoFactorLoginRoute($route)) {
            return Inertia::render('Auth/Admin/TwoFactorChallenge');
        }

        return view('auth.two_factor_challenge');
    }
}
