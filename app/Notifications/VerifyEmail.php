<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as AuthVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends AuthVerifyEmail
{
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(__('Verify Email Address'))
            ->markdown('emails.html.verify-email', ['url' => $url]);
    }
}
