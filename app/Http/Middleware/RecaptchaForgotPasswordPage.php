<?php

namespace App\Http\Middleware;

class RecaptchaForgotPasswordPage extends Recaptcha
{
    protected function getRecaptchaScore(): float
    {
        return $this->settingService->getForgotPasswordRecaptchaScore();
    }
}
