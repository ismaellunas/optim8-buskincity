<?php

namespace App\Actions\Fortify\View;

use App\Helpers\Url;
use App\Services\LoginService;
use App\Services\SettingService;
use App\Services\StorageService;
use Inertia\Inertia;

class TwoFactorChallengeView
{
    public function __invoke()
    {
        $url = url()->current();
        $route = Url::getRoute($url);

        $recaptchaKeys = app(SettingService::class)->getRecaptchaKeys();
        $recaptchaSiteKey = $recaptchaKeys['recaptcha_site_key'] ?? null;

        if (LoginService::isAdminTwoFactorLoginRoute($route)) {
            return Inertia::render('Auth/Admin/TwoFactorChallenge', [
                'media' => [
                    'default_card_image' => StorageService::getImageUrl(
                        config('constants.default_images.admin_auth_card')
                    )
                ],
                'recaptchaSiteKey' => $recaptchaSiteKey,
            ]);
        }

        return view('auth.two_factor_challenge', [
            'recaptchaSiteKey' => $recaptchaSiteKey,
        ]);
    }
}
