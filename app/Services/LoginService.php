<?php

namespace App\Services;

use Illuminate\Routing\Route;

class LoginService
{
    public static function isAdminLoginAttemptRoute(Route $route): bool
    {
        return $route->getName() == config('fortify.routes.admin_login_attempt');
    }
}
