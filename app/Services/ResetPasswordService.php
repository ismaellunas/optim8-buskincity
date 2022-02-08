<?php

namespace App\Services;

use App\Helpers\Url;
use App\Models\User;

class ResetPasswordService
{
    public static function getResetUrl(User $user, string $token): string
    {
        $url = url()->current();
        $route = Url::getRoute($url);
        $routeName = "password.reset";

        if ($route->getName() == config('fortify.routes.admin_forgot_password')) {
            $routeName = "admin.password.reset";
        }

        return route($routeName, $token).'?email='.$user->email;
    }
}
