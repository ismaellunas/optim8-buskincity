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

    private $formBuilderService;

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

        $this->formBuilderService = app(FormBuilderService::class);
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

        $fromEmail = $this->formBuilderService->validateEmail(
                $this->formBuilderService
                    ->swapTagWithEntryValue(
                        $entry,
                        $setting->from_email,
                    )
            ) ?? config('formbuilder.default_from_email');

        $fromName = $this->formBuilderService
            ->swapTagWithEntryValue(
                $entry,
                $setting->from_name
            );

        $replyToEmail = $this->formBuilderService->validateEmail(
                $this->formBuilderService
                    ->swapTagWithEntryValue(
                        $entry,
                        $setting->reply_to
                    )
            );

        $subject = $this->formBuilderService
            ->swapTagWithEntryValue(
                $entry,
                $setting->subject
            );

        $message = $this->formBuilderService
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
