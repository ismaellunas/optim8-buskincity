<?php

namespace Modules\Booking\Http\Controllers\Frontend;

use App\Http\Controllers\CrudController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Booking\Services\EventService;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Services\ProductService;

class ProductController extends CrudController
{
    protected $title = "Product";
    protected $baseRouteName = "booking.products";

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

        return Inertia::render('Booking::FrontendProductIndex', $this->getData([
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
        $schedule = $product->eventSchedule;
        $minDate = $this->productEventService->minBookableDate();
        $maxDate = $this->productEventService->maxBookableDate($product);

        return Inertia::render('Booking::FrontendProductShow', $this->getData([
            'title' => $product->translateAttribute('name', config('app.locale')),
            'allowedDatesRouteName' => $this->productEventService->allowedDatesRouteName(),
            'availableTimesRouteName' => $this->productEventService->availableTimesRouteName(),
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

        return $this->eventService->availableTimes($schedule, $dateTime);
    }

    public function allowedDates(Product $product, string $month, string $year)
    {
        $schedule = $product->eventSchedule;

        return $this->eventService->allowedDates(
            $schedule,
            $month,
            $year
        );
    }
}
