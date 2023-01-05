<?php

namespace App\Services;

use App\Models\ErrorLog;

class RecaptchaService
{
    public static function siteKeyError(): void
    {
        $message = __('reCAPTCHA site key failed. Please make sure the reCAPTCHA site key is valid.');

        $errorLog = new ErrorLog();

        $errorLog->syncErrorLog([
            'url' => url()->full(),
            'message' => $message,
        ]);
    }

    public static function secretKeyError(): void
    {
        $message = __('reCAPTCHA secret key failed. Please make sure the reCAPTCHA secret key is valid.');

        $errorLog = new ErrorLog();

        $errorLog->syncErrorLog([
            'url' => url()->full(),
            'message' => $message,
        ]);
    }
}