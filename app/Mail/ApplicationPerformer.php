<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ApplicationPerformer extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    private $fullName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->fullName = Str::title(
            $data['first_name'].' '.$data['last_name']
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this
            ->from($this->data['email'], $this->fullName)
            ->subject(__('Performer Application'))
            ->markdown('emails.html.application-performer', $this->data);

        foreach ($this->data['photos']['files'] as $file) {
            $mail = $mail->attach(
                $file['path'],
                [
                    'as' => $file['as'],
                    'mime' => $file['mime']
                ]
            );
        }

        return $mail;
    }
}
