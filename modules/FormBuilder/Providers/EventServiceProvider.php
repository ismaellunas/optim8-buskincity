<?php

namespace Modules\FormBuilder\Providers;

use App\Listeners\UnassignModulePermissions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\FormBuilder\Entities\Form;
use Modules\FormBuilder\Entities\FormEntry;
use Modules\FormBuilder\Events\FormSubmitted;
use Modules\FormBuilder\Events\ModuleDeactivated;
use Modules\FormBuilder\Listeners\DeactivateAllNotificationSettings;
use Modules\FormBuilder\Listeners\SendFormNotification;
use Modules\FormBuilder\Observers\FormEntryObserver;
use Modules\FormBuilder\Observers\FormObserver;

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
        ModuleDeactivated::class => [
            UnassignModulePermissions::class,
            DeactivateAllNotificationSettings::class,
        ],
    ];

    public function boot()
    {
        FormEntry::observe(FormEntryObserver::class);
        Form::observe(FormObserver::class);
    }
}
