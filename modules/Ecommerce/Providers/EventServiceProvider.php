<?php

namespace Modules\Ecommerce\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Ecommerce\Events\EventBooked;
use Modules\Ecommerce\Events\EventCanceled;
use Modules\Ecommerce\Events\EventRescheduled;
use Modules\Ecommerce\Listeners\SendBookedEventNotification;
use Modules\Ecommerce\Listeners\SendCanceledEventNotification;
use Modules\Ecommerce\Listeners\SendRescheduledEventNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        EventBooked::class => [
            SendBookedEventNotification::class,
        ],
        EventRescheduled::class => [
            SendRescheduledEventNotification::class,
        ],
        EventCanceled::class => [
            SendCanceledEventNotification::class,
        ],
    ];
}
