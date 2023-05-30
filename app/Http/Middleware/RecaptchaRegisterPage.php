<?php

namespace App\Http\Middleware;

class RecaptchaRegisterPage extends Recaptcha
{
    protected function getRecaptchaScore(): float
    {
        return $this->settingService->getRegisterRecaptchaScore();
    }
}
