<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ThankYouCheckoutCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public $amount;
    public $currency;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($amount, $currency)
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(__('Thanks For Your Donation'))
            ->markdown('emails.thankyou-checkout-completed', [
                'amount' => $this->amount,
                'currency' => $this->currency,
            ]);
    }
}
