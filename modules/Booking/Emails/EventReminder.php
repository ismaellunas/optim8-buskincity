<?php

namespace Modules\Booking\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Booking\Enums\BookingStatus;
use Modules\Ecommerce\Entities\Order;

class EventReminder extends Mailable
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
        $line = $this->order->firstEventLine;
        $user = $this->order->user;

        $upcomingEvent = $line
            ->events
            ->where('status', BookingStatus::UPCOMING->value)
            ->last();

        $schedule = $upcomingEvent->schedule;

        $eventDateTime = $upcomingEvent
            ->booked_at
            ->setTimezone($schedule->timezone);

        $inviteeName = $user->fullName ?? null;
        $productName = $line->purchasable->product->displayName;

        return $this
            ->subject( __('Reminder: :inviteeName - :startedTime :date - :productName', [
                'date' => $eventDateTime->format(config('ecommerce.format.date_event_email_title')),
                'inviteeName' => $inviteeName,
                'productName' => $productName,
                'startedTime' => $eventDateTime->format('H:i'),
            ]))
            ->markdown('booking::emails.event.reminder')
            ->with([
                'duration' => $upcomingEvent->displayDuration,
                'eventDateTime' => $eventDateTime->format(config('ecommerce.format.date_event_email_body')),
                'productName' => $productName,
                'startedTime' => $eventDateTime->format('H:i'),
                'timezone' => $schedule->timezone,
                'toName' => $this->to[0]['name'] ?? $this->to['address'] ?? "",
            ]);
    }
}
