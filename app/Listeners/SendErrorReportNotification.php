<?php

namespace App\Listeners;

use App\Mail\ErrorReportNotification;
use App\Events\ErrorReport;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendErrorReportNotification implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\RecaptchaError  $event
     * @return void
     */
    public function handle(ErrorReport $event)
    {
        $timeLog = $event->timeLog;
        $errorLogs = $event->errorLogs;

        if (!empty($errorLogs)) {
            $recipients = User::available()
                ->inRoleNames([
                    config('permission.role_names.admin'),
                    config('permission.role_names.super_admin'),
                ])
                ->pluck('email')
                ->toArray();

            foreach ($recipients as $recipient) {
                Mail::to($recipient)
                    ->queue(new ErrorReportNotification(
                        $timeLog,
                        $errorLogs
                    ));
            }
        }
    }
}
