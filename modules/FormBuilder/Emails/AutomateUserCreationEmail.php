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

    protected AutomateUserCreationService $automateUserCreationService;
    protected string $viewName = 'formbuilder::emails.automated_create_user.created';
    protected string $templateKey = 'email.automate_user_creation';

    public function __construct(
        protected User $user,
        protected Form $form
    ) {
        $this->automateUserCreationService = app(AutomateUserCreationService::class);
    }

    public function envelope()
    {
        return new Envelope(
            subject: Str::title(__('Your new :appName account is ready', [
                'appName' => config('app.name')
            ])),
        );
    }

    protected function composeBody(): string
    {
        $template = Arr::get($this->form->setting, $this->templateKey);

        return !empty($template)
            ? $this->automateUserCreationService->swapTagWithValue(
                $this->user,
                $template
            )
            : '';
    }

    public function content()
    {
        return new Content(
            markdown: $this->viewName,
            with: ['body' => Purifier::clean($this->composeBody(), 'email')]
        );
    }
}
