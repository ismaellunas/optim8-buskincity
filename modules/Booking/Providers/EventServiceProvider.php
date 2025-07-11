<?php

namespace Modules\Booking\Providers;

use App\Listeners\SanitizeDisabledComponentsOnPageTranslations;
use App\Listeners\UnassignModulePermissions;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Booking\Events\EventBooked;
use Modules\Booking\Events\EventCanceled;
use Modules\Booking\Events\EventRescheduled;
use Modules\Booking\Events\ModuleDeactivated;
use Modules\Booking\Listeners\CancelUpcomingOrOngoingBookings;
use Modules\Booking\Listeners\SendBookedEventNotification;
use Modules\Booking\Listeners\SendCanceledEventNotification;
use Modules\Booking\Listeners\SendRescheduledEventNotification;
use Modules\Booking\Listeners\SetPublishedProductsToDraft;
use Modules\Booking\Listeners\UnassignAllProductManagers;
use Modules\Booking\Listeners\UnassignSpaceFromProduct;
use Modules\Booking\Observers\ProductObserver;
use Modules\Ecommerce\Entities\Product;

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
        ModuleDeactivated::class => [
            UnassignModulePermissions::class,
            UnassignAllProductManagers::class,
            SetPublishedProductsToDraft::class,
            CancelUpcomingOrOngoingBookings::class,
            SanitizeDisabledComponentsOnPageTranslations::class,
            UnassignSpaceFromProduct::class,
        ],
    ];

    public function boot()
    {
        Product::observe(ProductObserver::class);
    }
}
