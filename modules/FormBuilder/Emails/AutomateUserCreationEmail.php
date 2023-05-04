<?php

namespace Modules\FormBuilder\Emails;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Services\AutomateUserCreationService;

class AutomateUserCreationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected User $user,
        protected Form $form
    ) {}

    public function envelope()
    {
        return new Envelope(
            subject: Str::title(__('Your new :appName account is ready', [
                'appName' => config('app.name')
            ])),
        );
    }

    public function content()
    {
        $body = app(AutomateUserCreationService::class)
            ->swapTagWithValue(
                $this->user,
                Arr::get($this->form->setting, 'email.automate_user_creation')
            );

        return new Content(
            markdown: 'formbuilder::emails.automated_create_user.created',
            with: ['body' => Purifier::clean($body, 'email')]
        );
    }
}
