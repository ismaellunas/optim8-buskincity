<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ErrorReportNotification extends Mailable
{
    use Queueable, SerializesModels;

    private $timeLog = null;
    private $errorLogs = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        string $timeLog,
        array $errorLogs
    ) {
        $this->timeLog = $timeLog;
        $this->errorLogs = $errorLogs;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $errorLogs = collect($this->errorLogs);

        return $this
            ->subject(__(config('app.name') . ' - Errors Report on ' . $this->timeLog))
            ->markdown('emails.html.error-report', [
                'errorLogs' => $errorLogs->all(),
                'totalError' => $errorLogs->count(),
            ]);
    }
}
