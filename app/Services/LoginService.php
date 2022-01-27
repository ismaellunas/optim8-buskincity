<?php

namespace App\Services;

use Illuminate\Routing\Route;

class LoginService
{
    public static function isAdminLoginAttemptRoute(Route $route): bool
    {
        return $route->getName() == config('fortify.routes.admin_login_attempt');
    }

    public static function isAdminTwoFactorLoginAttemptRoute(Route $route): bool
    {
        return $route->getName() == config('fortify.routes.admin_two_factor_login_attempt');
    }

    public static function isAdminLoginRoute(Route $route): bool
    {
        return $route->getName() == config('fortify.routes.admin_login');
    }

    public static function isAdminTwoFactorLoginRoute(Route $route): bool
    {
        return $route->getName() == config('fortify.routes.admin_two_factor_login');
    }
}
