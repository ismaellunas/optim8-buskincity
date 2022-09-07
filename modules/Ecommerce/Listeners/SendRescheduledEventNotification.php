<?php

namespace Modules\Ecommerce\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Modules\Ecommerce\Emails\EventRescheduled as EventRescheduledEmail;
use Modules\Ecommerce\Events\EventRescheduled;

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

        Mail::to($order->user)
            ->send(new EventRescheduledEmail($order));
    }
}
