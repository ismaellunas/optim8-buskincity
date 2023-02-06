<?php

namespace Modules\FormBuilder\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\FormBuilder\Emails\FormNotification;
use Modules\FormBuilder\Events\FormSubmitted;
use Modules\FormBuilder\Services\FormBuilderService;

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
     * @param FormSubmitted $event
     * @return void
     */
    public function handle(FormSubmitted $event)
    {
        $entry = $event->formEntry;
        $notificationSettings = $entry->form->activeNotificationSettings;

        foreach ($notificationSettings as $notificationSetting) {
            $recipients = app(FormBuilderService::class)
                ->sanitizeEmails(
                    json_decode($notificationSetting->send_to)
                );

            $bcc = app(FormBuilderService::class)
                ->sanitizeEmails(
                    json_decode($notificationSetting->bcc)
                );

            foreach ($recipients as $recipient) {
                Mail::to($recipient)
                    ->bcc($bcc)
                    ->queue(new FormNotification($entry, $notificationSetting));
            }
        }

    }
}
