<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RoleApplicationApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $roleLabel,
        public string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your :role application has been approved', [
                'role' => $this->roleLabel,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.role-application.approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
