<?php

namespace Modules\Booking\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Booking\Events\ModuleDeactivated;
use Modules\Booking\Services\PitchBookingService;

class UnassignAllProductManagers implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(protected PitchBookingService $pitchBookingService)
    {}

    public function handle(ModuleDeactivated $event)
    {
        $this
            ->pitchBookingService
            ->getManagedProducts()
            ->each(function ($product) {
                $product->managers()->detach();
            });
    }
}
