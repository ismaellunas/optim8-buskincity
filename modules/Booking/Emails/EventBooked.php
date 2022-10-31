<?php

namespace Modules\Booking\Emails;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Mews\Purifier\Facades\Purifier;
use Modules\Booking\Enums\BookingStatus;
use Modules\Ecommerce\Entities\Order;

class EventBooked extends Mailable
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

        $template = Setting::key('booking_email_new_booking')->value('value');

        return $this
            ->subject( __('New Event: :inviteeName - :startedTime :date - :productName', [
                'date' => $eventDateTime->format(config('ecommerce.format.date_event_email_title')),
                'inviteeName' => $inviteeName,
                'productName' => $productName,
                'startedTime' => $eventDateTime->format('H:i'),
            ]))
            ->markdown('booking::emails.event.booked')
            ->with([
                'duration' => $upcomingEvent->displayDuration,
                'eventDateTime' => $eventDateTime->format(config('ecommerce.format.date_event_email_body')),
                'inviteeEmail' => $user->email,
                'inviteeName' => $inviteeName,
                'productName' => $productName,
                'template' => Purifier::clean($template, 'email'),
                'timezone' => $schedule->timezone,
                'toName' => $this->to[0]['name'] ?? $this->to['address'] ?? "",
            ]);
    }
}
