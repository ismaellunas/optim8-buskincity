<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as AuthVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends AuthVerifyEmail
{
    protected $isAdmin = false;

    public function admin()
    {
        $this->isAdmin = true;

        return $this;
    }

    /** @override */
    protected function verificationUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        $verificationRoute = 'verification.verify';

        if ($this->isAdmin) {
            $verificationRoute = 'admin.verification.verify';
        }

        return URL::temporarySignedRoute(
            $verificationRoute,
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject(__('Verify Email Address'))
            ->markdown('emails.html.verify-email', ['url' => $url]);
    }
}
