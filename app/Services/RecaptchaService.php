<?php

namespace App\Services;

use App\Events\RecaptchaError;

class RecaptchaService
{
    public static function sendEmailSiteKeyError(): void
    {
        $message = __('Our **reCAPTCHA site key** failed. Please make sure the **reCAPTCHA site key** is valid.');

        RecaptchaError::dispatch($message);
    }

    public static function sendEmailSecretKeyError(): void
    {
        $message = __('Our **reCAPTCHA secret key** failed. Please make sure the **reCAPTCHA secret key** is valid.');

        RecaptchaError::dispatch($message);
    }
}