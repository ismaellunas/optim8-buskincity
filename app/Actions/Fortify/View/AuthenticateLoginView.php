<?php

namespace App\Actions\Fortify\View;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class AuthenticateLoginView
{
    public function __invoke()
    {
        if (Route::currentRouteName() == config('fortify.routes.admin_login')) {
            $componentName = 'Auth/Admin/Login';
            return Inertia::render($componentName, [
                'canResetPassword' => Route::has('password.request'),
                'status' => session('status'),
            ]);
        }

        return view('auth.login');

    }
}
