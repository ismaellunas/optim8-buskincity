<?php

namespace Modules\FormBuilder\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\FormBuilder\Events\FormNotification;
use Modules\FormBuilder\Listeners\SendFormNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        FormNotification::class => [
            SendFormNotification::class,
        ],
    ];
}
