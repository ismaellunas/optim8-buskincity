<?php

namespace Modules\FormBuilder\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\FormBuilder\Events\FormSubmitted;
use Modules\FormBuilder\Listeners\SendFormNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        FormSubmitted::class => [
            SendFormNotification::class,
        ],
    ];
}
