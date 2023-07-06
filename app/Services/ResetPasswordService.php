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
        $currentRouteName = $route ? $route->getName() : null;

        $routeName = "password.reset";

        if (
            $currentRouteName == config('fortify.routes.admin_forgot_password')
            || $user->can('system.dashboard')
        ) {
            $routeName = "admin.password.reset";
        }

        return route($routeName, [
            'token' => $token,
            'email' => $user->email
        ]);
    }
}
