<?php

namespace Modules\Booking\Http\Controllers\Frontend;

use App\Http\Controllers\CrudController;
use App\Services\SettingService;
use Illuminate\Contracts\Support\Renderable;
use Inertia\Inertia;
use Modules\Booking\Http\Requests\ProductIndexRequest;
use Modules\Booking\Services\EventService;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Services\ProductService;

class ProductController extends CrudController
{
    protected $title = "booking_module::terms.product";
    protected $baseRouteName = "booking.products";

    public function __construct(
        private EventService $eventService,
        private ProductEventService $productEventService,
        private ProductService $productService
    ) {}

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(ProductIndexRequest $request)
    {
        $user = auth()->user();

        $scopes = [
            'orderByColumn' => [
                'column' => $request->column,
                'order' => $request->order,
            ],
            'city' => $request->city ?? null,
            'country' => $request->country ?? null,
        ];

        return Inertia::render('Booking::FrontendProductIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'pageQueryParams' => array_filter($request->only(
                'term',
                'column',
                'order',
                'city',
                'country',
            )),
            'products' => $this->productService->getFrontendRecords(
                $user,
                $request->term,
                $scopes,
            ),
            'countryOptions' => $this->productEventService->getFrontendCountryOptions(),
            'cityOptions' => $this->productEventService->getFrontendCityOptions(),
            'i18n' => [
                'book_now' => __('Book now'),
            ],
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
            'i18n' => [
                'products' => __('booking_module::terms.products'),
                'event_booking' => __('Event booking'),
                'booking_event_confirmation' => __('Booking event confirmation'),
            ],
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
