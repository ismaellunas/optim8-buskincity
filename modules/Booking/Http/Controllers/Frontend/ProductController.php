<?php

namespace Modules\Booking\Http\Controllers\Frontend;

use App\Http\Controllers\CrudController;
use App\Services\SettingService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Booking\Http\Requests\ProductIndexRequest;
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
    public function index(ProductIndexRequest $request)
    {
        $user = auth()->user();

        return Inertia::render('Booking::FrontendProductIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'pageQueryParams' => array_filter($request->only('term', 'column', 'order')),
            'products' => $this->productService->getFrontendRecords(
                $user,
                $request->term,
                [
                    'orderByColumn' => [
                        'column' => $request->column,
                        'order' => $request->order,
                    ]
                ]
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
            'googleApiKey' => app(SettingService::class)->getGoogleApi(),
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
