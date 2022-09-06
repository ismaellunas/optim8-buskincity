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
        $line = $this->order->lines->first();
        $schedule = $line->scheduleBooking;

        $template = Setting::key('booking_email_cancellation')->value('value');

        return $this
            ->markdown('ecommerce::emails.orders.canceled')
            ->with([
                'line' => $line,
                'schedule' => $schedule,
                'template' => $template,
            ]);
    }
}
