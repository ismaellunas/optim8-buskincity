<?php

namespace Modules\Booking\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Modules\Booking\Events\EventRescheduled;
use Modules\Booking\Emails\EventRescheduled as EventRescheduledEmail;
use Modules\Ecommerce\Services\OrderService;

class SendRescheduledEventNotification implements ShouldQueue
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
    public function handle(EventRescheduled $event)
    {
        $order = $event->order;

        $recipients = app(OrderService::class)->emailReceipients($order);

        foreach ($recipients as $recipient) {
            Mail::to($recipient)
                ->queue(new EventRescheduledEmail($order));
        }
    }
}
