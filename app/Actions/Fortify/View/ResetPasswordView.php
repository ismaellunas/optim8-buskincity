<?php

namespace App\Actions\Fortify\View;

use App\Helpers\Url;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResetPasswordView
{
    public function __invoke(Request $request)
    {
        $url = url()->current();
        $route = Url::getRoute($url);

        if ($route->getName() == config('fortify.routes.admin_reset_password')) {

            return Inertia::render('Auth/Admin/ResetPassword', [
                'email' => $request->input('email'),
                'token' => $request->route('token'),
            ]);

        }

        return view('auth.reset-password', [
            'email' => $request->input('email'),
            'token' => $request->route('token'),
        ]);
    }
}
