<?php

namespace Modules\Booking\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Modules\Booking\Emails\EventBooked as EventBookedEmail;
use Modules\Booking\Events\EventBooked;
use Modules\Ecommerce\Services\OrderService;

class SendBookedEventNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param EventRescheduled $event
     * @return void
     */
    public function handle(EventBooked $event)
    {
        $order = $event->order;

        $recipients = app(OrderService::class)->emailReceipients($order);

        foreach ($recipients as $recipient) {
            Mail::to($recipient)
                ->queue(new EventBookedEmail($order));
        }
    }
}
