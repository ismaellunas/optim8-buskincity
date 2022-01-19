<?php

namespace App\Actions;

use Inertia\Inertia;

class TwoFactorChallengeView
{
    public function __invoke()
    {
        $componentName = 'Auth/TwoFactorChallenge';

        $url = url()->previous();
        $route = app('router')->getRoutes($url)->match(app('request')->create($url));
        $prevRouteName = $route->getName();

        if ($prevRouteName === config('fortify.routes.admin_login')) {
            $componentName = 'Auth/Admin/TwoFactorChallenge';
        }

        return Inertia::render($componentName);
    }
}
