<?php

namespace App\Actions\Fortify\View;

use App\Helpers\Url;
use App\Services\LoginService;
use Inertia\Inertia;

class TwoFactorChallengeView
{
    public function __invoke()
    {
        $componentName = 'Auth/TwoFactorChallenge';

        $url = url()->current();
        $route = Url::getRoute($url);

        if (LoginService::isAdminTwoFactorLoginRoute($route)) {
            $componentName = 'Auth/Admin/TwoFactorChallenge';
        }

        return Inertia::render($componentName);
    }
}
