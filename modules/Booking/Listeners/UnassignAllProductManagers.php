<?php

namespace Modules\Booking\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Booking\Events\ModuleDeactivated;
use Modules\Booking\Services\ProductEventService;

class UnassignAllProductManagers implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(protected ProductEventService $productEventService)
    {}

    public function handle(ModuleDeactivated $event)
    {
        $this
            ->productEventService
            ->getManagedProducts()
            ->each(function ($product) {
                $product->managers()->detach();
            });
    }
}
