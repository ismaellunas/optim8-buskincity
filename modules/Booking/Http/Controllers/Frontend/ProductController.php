<?php

namespace Modules\Booking\Http\Controllers\Frontend;

use App\Http\Controllers\CrudController;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Inertia\Inertia;
use Modules\Booking\Http\Requests\ProductIndexRequest;
use Modules\Booking\Services\EventService;
use Modules\Booking\Services\ProductEventService;
use Modules\Booking\Entities\ProductEvent;
use App\Enums\PublishingStatus;
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
        $productEvents = $product->productEvents()
            ->published()
            ->with('schedule')
            ->orderBy('started_at')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'started_at' => $event->started_at->toIso8601String(),
                    'ended_at' => $event->ended_at->toIso8601String(),
                    'timezone' => $event->timezone,
                    'schedule_timezone' => $event->schedule?->timezone,
                    'location' => [
                        'address' => $event->address,
                        'city' => $event->city,
                        'country_code' => $event->country_code,
                        'latitude' => $event->latitude,
                        'longitude' => $event->longitude,
                    ],
                ];
            })
            ->all();

        $canBook = !empty($productEvents);
        $minDate = $canBook ? $this->productEventService->minBookableDate($product) : null;
        $maxDate = $canBook ? $this->productEventService->maxBookableDate($product) : null;

        return Inertia::render('Booking::FrontendProductShow', $this->getData([
            'title' => $product->translateAttribute('name', config('app.locale')),
            'allowedDatesRouteName' => $this->productEventService->allowedDatesRouteName(),
            'availableTimesRouteName' => $this->productEventService->availableTimesRouteName(),
            'event' => $this->productEventService->detailResource($product),
            'maxDate' => $maxDate?->toDateString(),
            'minDate' => $minDate?->toDateString(),
            'product' => $this->productService->productDetailResource($product),
            'productEvents' => $productEvents,
            'timezone' => $schedule->timezone,
            'canBook' => $canBook,
            'noEventsMessage' => __('No events available for booking at this time.'),
            'googleApiKey' => app(SettingService::class)->getGoogleApi(),
            'i18n' => [
                'products' => __('booking_module::terms.products'),
                'event_booking' => __('Event booking'),
                'booking_event_confirmation' => __('Booking event confirmation'),
            ],
        ]));
    }

    public function availableTimes(Request $request, Product $product, string $dateTime)
    {
        $productEventId = $request->get('product_event_id');

        if (empty($productEventId)) {
            return collect();
        }

        $productEvent = ProductEvent::where('product_id', $product->id)
            ->where('status', PublishingStatus::PUBLISHED->value)
            ->find($productEventId);

        if (!$productEvent || !$productEvent->schedule) {
            return collect();
        }

        $date = \Carbon\Carbon::parse($dateTime);
        if ($date->lt($productEvent->started_at) || $date->gt($productEvent->ended_at)) {
            return collect();
        }

        return $this->eventService->availableTimes($productEvent->schedule, $dateTime);
    }

    public function allowedDates(Request $request, Product $product, string $month, string $year)
    {
        $productEventId = $request->get('product_event_id');

        if (empty($productEventId)) {
            return collect();
        }

        $productEvent = ProductEvent::where('product_id', $product->id)
            ->where('status', PublishingStatus::PUBLISHED->value)
            ->find($productEventId);

        if (!$productEvent || !$productEvent->schedule) {
            return collect();
        }

        $allowedDates = $this->eventService->allowedDates(
            $productEvent->schedule,
            $month,
            $year
        );

        $startDate = $productEvent->started_at->toDateString();
        $endDate = $productEvent->ended_at->toDateString();

        return $allowedDates->filter(function ($date) use ($startDate, $endDate) {
            return $date >= $startDate && $date <= $endDate;
        })->values();
    }
}
