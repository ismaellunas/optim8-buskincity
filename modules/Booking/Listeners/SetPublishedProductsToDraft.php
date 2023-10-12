<?php

namespace Modules\Booking\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\Booking\Events\ModuleDeactivated;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Enums\ProductStatus;

class SetPublishedProductsToDraft implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(protected ProductEventService $productEventService)
    {}

    public function handle(ModuleDeactivated $event)
    {
        $this
            ->productEventService
            ->getPublishedProducts()
            ->each(function ($product) {
                $product->status = ProductStatus::DRAFT;
                $product->save();
            });
    }
}
