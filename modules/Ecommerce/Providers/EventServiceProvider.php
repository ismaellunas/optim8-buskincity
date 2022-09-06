<?php

namespace Modules\Ecommerce\Providers;

use Modules\Ecommerce\Events\OrderCanceled;
use Modules\Ecommerce\Listeners\SendCancelOrderNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        OrderCanceled::class => [
            SendCancelOrderNotification::class,
        ],
    ];
}
