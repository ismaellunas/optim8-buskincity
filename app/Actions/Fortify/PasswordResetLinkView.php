<?php

namespace App\Actions\Fortify;

use Inertia\Inertia;

class PasswordResetLinkView
{
    public function __invoke()
    {
        return Inertia::render('Auth/ForgotPassword', [
            'recaptchaSiteKey' => env('RECAPTCHA_SITE_KEY'),
            'failed' => request()->session()->get('failed'),
            'status' => request()->session()->get('status'),
        ]);
    }
}
