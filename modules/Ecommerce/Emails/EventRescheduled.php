<?php

namespace Modules\Ecommerce\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Enums\BookingStatus;

class EventRescheduled extends Mailable
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

        $rescheduledEvent = $line
            ->events
            ->where('status', BookingStatus::RESCHEDULED->value)
            ->last();

        $upcomingEvent = $line
            ->events
            ->where('status', BookingStatus::UPCOMING->value)
            ->last();

        return $this
            ->subject( __('Updated Event: :productName @ :date :startedTime - :endedTime', [
                'productName' => $line->purchasable->product->translateAttribute('name'),
                'date' => $upcomingEvent->booked_at->toDateString(),
                'startedTime' => $upcomingEvent->booked_at->format('H:i'),
                'endedTime' => $upcomingEvent->ended_time->format('H:i'),
            ]))
            ->markdown('ecommerce::emails.orders.rescheduled')
            ->with([
                'line' => $line,
                'rescheduledEvent' => $rescheduledEvent,
                'upcomingEvent' => $upcomingEvent,
            ]);
    }
}
