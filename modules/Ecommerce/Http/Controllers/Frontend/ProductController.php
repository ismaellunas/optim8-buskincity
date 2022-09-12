<?php

namespace Modules\Ecommerce\Http\Controllers\Frontend;

use App\Http\Controllers\CrudController;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Services\EventService;
use Modules\Ecommerce\Services\ProductEventService;
use Modules\Ecommerce\Services\ProductService;

class ProductController extends CrudController
{
    protected $title = "Product";
    protected $baseRouteName = "ecommerce.products";

    private $eventService;
    private $productEventService;
    private $productService;

    public function __construct(
        EventService $eventService,
        ProductEventService $productEventService,
        ProductService $productService
    ) {
        $this->eventService = $eventService;
        $this->productService = $productService;
        $this->productEventService = $productEventService;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        return Inertia::render('Ecommerce::FrontendProductIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'pageQueryParams' => array_filter($request->only('term')),
            'products' => $this->productService->getFrontendRecords(
                $user,
                $request->term
            ),
        ]));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Product $product)
    {
        $typeName = Str::title($product->productType->name);

        $method = "product{$typeName}Show";

        return $this->$method($product);
    }

    private function productEventShow(Product $product)
    {
        $schedule = $product->eventSchedule;
        $minDate = $this->productEventService->minBookableDate();
        $maxDate = $this->productEventService->maxBookableDate($product);

        $disabledDates = $this->eventService->disabledDates($schedule, $minDate, $maxDate);

        return Inertia::render('Ecommerce::FrontendProductShow', $this->getData([
            'title' => $product->translateAttribute('name', config('app.locale')),
            'description' => $product->sku,
            'disabledDates' => $disabledDates,
            'event' => $this->productEventService->detailResource($product),
            'maxDate' => $maxDate->toDateString(),
            'minDate' => $minDate->toDateString(),
            'product' => $this->productService->productDetailResource($product),
            'timezone' => $schedule->timezone,
        ]));
    }

    public function availableTimes(Product $product, string $dateTime)
    {
        $schedule = $product->eventSchedule;

        return $this->eventService->availableTimes(
            $schedule,
            Carbon::parse($dateTime)
        );
    }
}
