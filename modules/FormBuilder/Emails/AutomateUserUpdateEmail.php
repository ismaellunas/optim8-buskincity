<?php

namespace Modules\FormBuilder\Emails;

use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Str;

class AutomateUserUpdateEmail extends AutomateUserCreationEmail
{
    protected string $viewName = 'formbuilder::emails.automated_create_user.created';
    protected string $templateKey = 'email.automate_user_update';

    public function envelope()
    {
        return new Envelope(
            subject: Str::title(__('Your :appName profile has been updated', [
                'appName' => config('app.name')
            ])),
        );
    }
}
