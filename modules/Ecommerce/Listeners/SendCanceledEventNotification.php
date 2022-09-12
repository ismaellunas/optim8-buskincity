<?php

namespace Modules\Ecommerce\Listeners;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Modules\Ecommerce\Emails\EventCanceled as EventCanceledEmail;
use Modules\Ecommerce\Events\EventCanceled;
use Modules\Ecommerce\Services\OrderService;

class SendCanceledEventNotification implements ShouldQueue
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
     * @param EventCanceled $event
     * @return void
     */
    public function handle(EventCanceled $event)
    {
        $order = $event->order;

        $recipients = app(OrderService::class)->emailReceipients($order);

        foreach ($recipients as $recipient) {
            Mail::to($recipient)
                ->queue(new EventCanceledEmail($order));
        }
    }
}
