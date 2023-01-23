<?php

namespace Modules\FormBuilder\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Mews\Purifier\Facades\Purifier;
use Modules\FormBuilder\Entities\FormEntry;
use Modules\FormBuilder\Entities\FormNotificationSetting;
use Modules\FormBuilder\Services\FormBuilderService;

class FormNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $entry;
    public $setting;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        FormEntry $entry,
        FormNotificationSetting $setting
    ) {
        $this->entry = $entry;
        $this->setting = $setting;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $entry = $this->entry;
        $setting = $this->setting;

        $fromEmail = $setting->from_email ?? config('formbuilder.default_from_email');
        $fromName = app(FormBuilderService::class)
            ->swapTagWithEntryValue(
                $entry,
                $setting->from_name
            );

        $replyToEmail = app(FormBuilderService::class)
            ->swapTagWithEntryValue(
                $entry,
                $setting->reply_to
            );

        $subject = app(FormBuilderService::class)
            ->swapTagWithEntryValue(
                $entry,
                $setting->subject
            );

        $message = app(FormBuilderService::class)
            ->swapTagWithEntryValue(
                $entry,
                $setting->message
            );

        return $this
            ->from($fromEmail, $fromName)
            ->replyTo($replyToEmail)
            ->subject($subject)
            ->markdown('formbuilder::emails.forms.notification')
            ->with([
                'message' => Purifier::clean($message, 'email'),
            ]);
    }
}
