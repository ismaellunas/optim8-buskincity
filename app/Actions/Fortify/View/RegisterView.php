<?php

namespace App\Actions\Fortify\View;

use App\Services\SettingService;

class RegisterView
{
    public function __invoke()
    {
        $recaptchaKeys = app(SettingService::class)->getRecaptchaKeys();

        return view('auth.register', [
            'recaptchaSiteKey' => $recaptchaKeys['recaptcha_site_key'] ?? null,
        ]);
    }
}