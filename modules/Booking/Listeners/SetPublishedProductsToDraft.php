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

    private $chunk = 200;

    public function __construct(protected ProductEventService $productEventService)
    {}

    public function handle(ModuleDeactivated $event)
    {
        $chunkedProducts = $this
            ->productEventService
            ->getPublishedProducts()
            ->chunk($this->chunk);

        foreach ($chunkedProducts as $products) {
            $products->load('metas');

            $products->each(function ($product) {
                $product->status = ProductStatus::DRAFT;
                $product->save();
            });
        }
    }
}
