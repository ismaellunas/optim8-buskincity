<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as AuthResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends AuthResetPassword
{
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(__('Reset Password Notification'))
            ->markdown('emails.html.reset-password', ['url' => $url]);
    }
}
