<?php

namespace Modules\Ecommerce\Providers;

use Modules\Ecommerce\Events\OrderCanceled;
use Modules\Ecommerce\Events\EventRescheduled;
use Modules\Ecommerce\Listeners\SendCancelOrderNotification;
use Modules\Ecommerce\Listeners\SendRescheduledEventNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        EventRescheduled::class => [
            SendRescheduledEventNotification::class,
        ],
        OrderCanceled::class => [
            SendCancelOrderNotification::class,
        ],
    ];
}
