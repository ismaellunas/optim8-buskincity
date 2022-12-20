<?php

namespace App\Listeners;

use App\Mail\RecaptchaErrorNotification;
use App\Events\RecaptchaError;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendRecaptchaErrorNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\RecaptchaError  $event
     * @return void
     */
    public function handle(RecaptchaError $event)
    {
        $message = $event->message;
        $recipients = User::available()
            ->inRoleNames([
                config('permission.role_names.admin'),
                config('permission.role_names.super_admin'),
            ])
            ->pluck('email')
            ->toArray();

        foreach ($recipients as $recipient) {
            Mail::to($recipient)
                ->queue(new RecaptchaErrorNotification($message));
        }
    }
}
