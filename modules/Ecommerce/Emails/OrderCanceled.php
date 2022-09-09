<?php

namespace Modules\Ecommerce\Emails;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Ecommerce\Entities\Order;

class OrderCanceled extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $line = $this->order->firstEventline;

        $event = $line->latestEvent;

        $template = Setting::key('booking_email_cancellation')->value('value');

        return $this
            ->subject(__('An event has been canceled'))
            ->markdown('ecommerce::emails.orders.canceled')
            ->with([
                'line' => $line,
                'event' => $event,
                'template' => $template,
            ]);
    }
}
