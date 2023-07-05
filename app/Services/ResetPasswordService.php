<?php

namespace App\Services;

use App\Models\User;

class ResetPasswordService
{
    public static function getResetUrl(User $user, string $token): string
    {
        $currentRouteName = request()->route()->getName();
        $routeName = "password.reset";

        if (
            $currentRouteName == config('fortify.routes.admin_forgot_password')
            || (
                $currentRouteName == 'admin.users.password-reset.send'
                && $user->can('system.dashboard')
            )
        ) {
            $routeName = "admin.password.reset";
        }

        return route($routeName, [
            'token' => $token,
            'email' => $user->email
        ]);
    }
}
