<?php

namespace Modules\FormBuilder\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Modules\FormBuilder\Emails\FormNotification as FormNotificationEmail;
use Modules\FormBuilder\Events\FormNotification;

class SendFormNotification implements ShouldQueue
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
     * @param FormNotification $event
     * @return void
     */
    public function handle(FormNotification $event)
    {
        $entry = $event->fieldGroupEntry;
        $notificationSettings = $entry->fieldGroup->activeNotificationSettings;

        foreach ($notificationSettings as $notificationSetting) {
            $recipients = json_decode($notificationSetting->send_to);
            $bcc = json_decode($notificationSetting->bcc);

            foreach ($recipients as $recipient) {
                Mail::to($recipient)
                    ->bcc($bcc)
                    ->queue(new FormNotificationEmail($entry, $notificationSetting));
            }
        }

    }
}
