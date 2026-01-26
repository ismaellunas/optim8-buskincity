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
        $minDate = $this->productEventService->minBookableDate($product);
        $maxDate = $this->productEventService->maxBookableDate($product);
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

        return Inertia::render('Booking::FrontendProductShow', $this->getData([
            'title' => $product->translateAttribute('name', config('app.locale')),
            'allowedDatesRouteName' => $this->productEventService->allowedDatesRouteName(),
            'availableTimesRouteName' => $this->productEventService->availableTimesRouteName(),
            'event' => $this->productEventService->detailResource($product),
            'maxDate' => $maxDate->toDateString(),
            'minDate' => $minDate->toDateString(),
            'product' => $this->productService->productDetailResource($product),
            'productEvents' => $productEvents,
            'timezone' => $schedule->timezone,
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

        $schedule = $product->eventSchedule;
        $productEvent = null;

        if (!empty($productEventId)) {
            $productEvent = ProductEvent::where('product_id', $product->id)
                ->find($productEventId);

            if ($productEvent && $productEvent->schedule) {
                $schedule = $productEvent->schedule;
            }
        }

        if ($productEvent) {
            $date = \Carbon\Carbon::parse($dateTime);
            if ($date->lt($productEvent->started_at) || $date->gt($productEvent->ended_at)) {
                return collect();
            }
        } else {
            $pitchStart = $product->getMeta('pitch_started_at');
            $pitchEnd = $product->getMeta('pitch_ended_at');
            if ($pitchStart || $pitchEnd) {
                $date = \Carbon\Carbon::parse($dateTime);
                if ($pitchStart && $date->lt(\Carbon\Carbon::parse($pitchStart))) {
                    return collect();
                }
                if ($pitchEnd && $date->gt(\Carbon\Carbon::parse($pitchEnd))) {
                    return collect();
                }
            }
        }

        return $this->eventService->availableTimes($schedule, $dateTime);
    }

    public function allowedDates(Request $request, Product $product, string $month, string $year)
    {
        $productEventId = $request->get('product_event_id');

        $schedule = $product->eventSchedule;
        $productEvent = null;

        if (!empty($productEventId)) {
            $productEvent = ProductEvent::where('product_id', $product->id)
                ->find($productEventId);

            if ($productEvent && $productEvent->schedule) {
                $schedule = $productEvent->schedule;
            }
        }

        $allowedDates = $this->eventService->allowedDates(
            $schedule,
            $month,
            $year
        );

        if ($productEvent) {
            $startDate = $productEvent->started_at->toDateString();
            $endDate = $productEvent->ended_at->toDateString();

            return $allowedDates->filter(function ($date) use ($startDate, $endDate) {
                return $date >= $startDate && $date <= $endDate;
            })->values();
        }

        $pitchStart = $product->getMeta('pitch_started_at');
        $pitchEnd = $product->getMeta('pitch_ended_at');
        if ($pitchStart || $pitchEnd) {
            $startDate = $pitchStart ? \Carbon\Carbon::parse($pitchStart)->toDateString() : null;
            $endDate = $pitchEnd ? \Carbon\Carbon::parse($pitchEnd)->toDateString() : null;

            return $allowedDates->filter(function ($date) use ($startDate, $endDate) {
                if ($startDate && $date < $startDate) {
                    return false;
                }
                if ($endDate && $date > $endDate) {
                    return false;
                }
                return true;
            })->values();
        }

        return $allowedDates;
    }
}
