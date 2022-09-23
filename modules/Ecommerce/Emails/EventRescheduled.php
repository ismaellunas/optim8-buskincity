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

        $schedule = $rescheduledEvent->schedule;

        $inviteeName = $this->order->user->fullName ?? null;
        $productName = $line->purchasable->product->translateAttribute('name');
        $upcomingEventDateTime = $upcomingEvent
            ->booked_at
            ->setTimezone($schedule->timezone);

        return $this
            ->subject( __('Updated: :inviteeName - :startedTime :date - :productName', [
                'productName' => $productName,
                'date' => $upcomingEventDateTime->format(config('ecommerce.format.date_event_email_title')),
                'startedTime' => $upcomingEventDateTime->format('H:i'),
                'inviteeName' => $inviteeName,
            ]))
            ->markdown('ecommerce::emails.event.rescheduled')
            ->with([
                'duration' => $upcomingEvent->displayDuration,
                'inviteeEmail' => $this->order->user->email,
                'inviteeName' => $inviteeName,
                'productName' => $productName,
                'updated' => [
                    'event_date_time' => $upcomingEventDateTime
                        ->format(config('ecommerce.format.date_event_email_body')),
                ],
                'former' => [
                    'event_date_time' => $rescheduledEvent
                        ->booked_at
                        ->setTimezone($schedule->timezone)
                        ->format(config('ecommerce.format.date_event_email_body')),
                ],
                'timezone' => $schedule->timezone,
                'toName' => $this->to[0]['name'] ?? $this->to['address'] ?? "",
            ]);
    }
}
