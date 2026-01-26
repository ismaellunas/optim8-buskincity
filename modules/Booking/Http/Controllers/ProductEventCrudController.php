<?php

namespace Modules\Booking\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Booking\Entities\ProductEvent;
use Modules\Booking\Http\Requests\ProductEventCrudRequest;
use Modules\Booking\Services\ProductEventCrudService;
use Modules\Ecommerce\Entities\Product;

class ProductEventCrudController extends Controller
{
    private $title = 'booking_module::terms.product booking_module::terms.event';

    public function __construct(
        private ProductEventCrudService $productEventService
    ) {}

    public function store(ProductEventCrudRequest $request, Product $product)
    {
        $inputs = $request->validated();

        $event = $this->productEventService->createEvent($product, $inputs);

        return [
            'event' => $this->productEventService->getEditableRecord($event),
            'message' => __('The :resource was created!', ['resource' => __($this->title)]),
        ];
    }

    public function update(ProductEventCrudRequest $request, Product $product, ProductEvent $productEvent)
    {
        if ($productEvent->product_id !== $product->id) {
            abort(404);
        }

        $inputs = $request->validated();

        $this->productEventService->updateEvent($productEvent, $inputs);

        return [
            'event' => $this->productEventService->getEditableRecord($productEvent),
            'message' => __('The :resource was updated!', ['resource' => __($this->title)]),
        ];
    }

    public function show(Product $product, ProductEvent $productEvent)
    {
        if ($productEvent->product_id !== $product->id) {
            abort(404);
        }

        return $this->productEventService->getEditableRecord($productEvent);
    }

    public function destroy(Product $product, ProductEvent $productEvent)
    {
        if ($productEvent->product_id !== $product->id) {
            abort(404);
        }

        $productEvent->delete();

        return response(__('The :resource was deleted!', ['resource' => __($this->title)]), 200);
    }

    public function records(Request $request, Product $product)
    {
        $records = $this->productEventService->getRecords($product, $request->term);

        $this->productEventService->transformRecords($records);

        return $records;
    }
}
