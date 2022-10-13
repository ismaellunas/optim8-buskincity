<?php

namespace Modules\Booking\Emails;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Ecommerce\Entities\Order;

class EventCanceled extends Mailable
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
        $event = $line->latestEvent;
        $schedule = $event->schedule;

        $eventDateTime = $event
            ->booked_at
            ->setTimezone($schedule->timezone);

        $inviteeName = $this->order->user->fullName ?? null;
        $productName = $line->purchasable->product->translateAttribute('name');
        $template = Setting::key('booking_email_cancellation')->value('value');

        return $this
            ->subject(__('Canceled: :productName with :inviteeName on :date', [
                'productName' => $productName,
                'date' => $eventDateTime->format('j F Y'),
                'startedTime' => $eventDateTime->format('H:i'),
                'inviteeName' => $inviteeName,
            ]))
            ->markdown('booking::emails.event.canceled')
            ->with([
                'duration' => $event->displayDuration,
                'eventDateTime' => $eventDateTime->format(config('ecommerce.format.date_event_email_body')),
                'inviteeEmail' => $this->order->user->email,
                'inviteeName' => $inviteeName,
                'productName' => $productName,
                'template' => $template,
                'timezone' => $schedule->timezone,
                'toName' => $this->to[0]['name'] ?? $this->to['address'] ?? "",
                'message' => $event->message,
            ]);
    }
}
