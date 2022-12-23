<?php

namespace App\Actions\Fortify\View;

use App\Helpers\Url;
use App\Services\SettingService;
use Inertia\Inertia;

class PasswordResetLinkView
{
    public function __invoke()
    {
        $url = url()->current();
        $route = Url::getRoute($url);

        $recaptchaKeys = app(SettingService::class)->getRecaptchaKeys();
        $recaptchaSiteKey = $recaptchaKeys['recaptcha_site_key'] ?? null;

        if ($route->getName() == config('fortify.routes.admin_forgot_password')) {

            return Inertia::render('Auth/Admin/ForgotPassword', [
                'failed' => request()->session()->get('failed'),
                'status' => request()->session()->get('status'),
                'recaptchaSiteKey' => $recaptchaSiteKey,
            ]);

        }

        return view('auth.forgot_password');
    }
}
