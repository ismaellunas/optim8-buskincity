<?php

namespace App\Actions\Fortify\View;

use App\Helpers\Url;
use Inertia\Inertia;

class PasswordResetLinkView
{
    public function __invoke()
    {
        $componentName = 'Auth/ForgotPassword';

        $url = url()->current();
        $route = Url::getRoute($url);

        if ($route->getName() == config('fortify.routes.admin_forgot_password')) {
            $componentName = 'Auth/Admin/ForgotPassword';

            return Inertia::render($componentName, [
                'recaptchaSiteKey' => env('RECAPTCHA_SITE_KEY'),
                'failed' => request()->session()->get('failed'),
                'status' => request()->session()->get('status'),
            ]);
        }

        return view('auth.forgot_password');
    }
}
