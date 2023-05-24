<?php

namespace App\Actions\Fortify\View;

use App\Services\LoginService;
use App\Services\SettingService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class AuthenticateLoginView
{
    public function __invoke()
    {
        $recaptchaKeys = app(SettingService::class)->getRecaptchaKeys();
        $recaptchaSiteKey = $recaptchaKeys['recaptcha_site_key'] ?? null;

        if (Route::currentRouteName() == config('fortify.routes.admin_login')) {
            $componentName = 'Auth/Admin/Login';
            return Inertia::render($componentName, [
                'canResetPassword' => Route::has('password.request'),
                'status' => session('status'),
                'recaptchaSiteKey' => $recaptchaSiteKey,
            ]);
        }

        return view('auth.login', [
            'availableSocialiteDrivers' => app(LoginService::class)->getAvailableSocialiteDrivers()
        ]);
    }
}
