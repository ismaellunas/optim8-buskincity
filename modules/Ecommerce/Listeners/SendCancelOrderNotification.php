<?php

namespace Modules\Ecommerce\Listeners;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Modules\Ecommerce\Emails\OrderCanceled as EmailsOrderCanceled;
use Modules\Ecommerce\Events\OrderCanceled;

class SendCancelOrderNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
        \Log::info('asdf asdf');
    }

    /**
     * Handle the event.
     *
     * @param OrderCanceled $event
     * @return void
     */
    public function handle(OrderCanceled $event)
    {
        $order = $event->order;

        $ccUsers = User::inRoleNames([config('permission.role_names.admin')])
            ->get();

        Mail::to($order->user)
            ->cc($ccUsers)
            ->send(new EmailsOrderCanceled($order));
    }
}
