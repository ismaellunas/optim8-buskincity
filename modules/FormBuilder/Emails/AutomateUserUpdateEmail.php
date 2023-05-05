<?php

namespace Modules\FormBuilder\Emails;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
use Modules\FormBuilder\Services\AutomateUserCreationService;

class AutomateUserUpdateEmail extends AutomateUserCreationEmail
{
    public function envelope()
    {
        return new Envelope(
            subject: Str::title(__('Your :appName profile has been updated', [
                'appName' => config('app.name')
            ])),
        );
    }

    public function content()
    {
        $body = app(AutomateUserCreationService::class)
            ->swapTagWithValue(
                $this->user,
                Arr::get($this->form->setting, 'email.automate_user_update'),
            );

        return new Content(
            markdown: 'formbuilder::emails.automated_create_user.created',
            with: ['body' => Purifier::clean($body, 'email')]
        );
    }
}
