<?php

namespace App\Http\Middleware;

class RecaptchaRegisterPage extends Recaptcha
{
    protected function getRecaptchaScore(): float
    {
        $recaptchaScores = $this->settingService->getRecaptchaScores();

        return (float)$recaptchaScores['recaptcha_score_register']
            ?? config('constants.settings.recaptcha.score');
    }
}
