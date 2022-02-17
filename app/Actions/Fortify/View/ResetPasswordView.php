<?php

namespace App\Actions\Fortify\View;

use App\Helpers\Url;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResetPasswordView
{
    public function __invoke(Request $request)
    {
        $componentName = 'Auth/ResetPassword';

        $url = url()->current();
        $route = Url::getRoute($url);

        if ($route->getName() == config('fortify.routes.admin_reset_password')) {
            $componentName = 'Auth/Admin/ResetPassword';
        }

        return Inertia::render($componentName, [
            'email' => $request->input('email'),
            'token' => $request->route('token'),
        ]);
    }
}
