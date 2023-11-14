<?php

namespace Modules\Booking\Listeners;

use Modules\Booking\Events\ModuleDeactivated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Booking\Services\ProductSpaceService;

class UnassignSpaceFromProduct implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ModuleDeactivated $event)
    {
        app(ProductSpaceService::class)->unassignSpaceFromProducts();
    }
}
