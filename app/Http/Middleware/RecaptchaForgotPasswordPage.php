<?php

namespace App\Http\Middleware;

class RecaptchaForgotPasswordPage extends Recaptcha
{
    protected function getRecaptchaScore(): float
    {
        $recaptchaScores = $this->settingService->getRecaptchaScores();

        return (float)$recaptchaScores['recaptcha_score_forgot_password']
            ?? config('constants.settings.recaptcha.score');
    }
}
