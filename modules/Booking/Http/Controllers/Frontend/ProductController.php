<?php

namespace Modules\Booking\Http\Controllers\Frontend;

use App\Http\Controllers\CrudController;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Booking\Http\Requests\ProductIndexRequest;
use Modules\Booking\Services\EventService;
use Modules\Booking\Services\PitchBookingService;
use Modules\Booking\Services\PitchListingService;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Services\ProductService;

class ProductController extends CrudController
{
    protected $title = "booking_module::terms.product";
    protected $baseRouteName = "booking.products";

    public function __construct(
        private EventService $eventService,
        private PitchBookingService $pitchBookingService,
        private ProductService $productService,
        private PitchListingService $pitchListingService,
    ) {}

    public function index(ProductIndexRequest $request)
    {
        $scopes = [
            'orderByColumn' => [
                'column' => $request->column,
                'order' => $request->order,
            ],
            'city' => $request->city ?? null,
            'country' => $request->country ?? null,
        ];

        $user = auth()->user();

        return Inertia::render('Booking::FrontendProductIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'pageQueryParams' => array_filter($request->only(
                'term',
                'column',
                'order',
                'city',
                'country',
            )),
            'baseRouteName' => 'booking.products',
            'events' => tap(
                $this->pitchListingService->getFrontendRecords(
                    $user,
                    $request->term,
                    $scopes,
                ),
                fn ($records) => $this->pitchListingService->transformFrontendRecords($records),
            ),
            'countryOptions' => $this->pitchListingService->getFrontendCountryOptions($user),
            'cityOptions' => $this->pitchListingService->getFrontendCityOptions($user),
            'i18n' => [
                'book_now' => __('Book now'),
            ],
        ]));
    }

    public function show(Product $product)
    {
        $schedule = $product->eventSchedule;

        if (! $schedule) {
            abort(404, 'Pitch schedule not configured');
        }

        $canBook = $this->pitchListingService->hasAvailableTimeslot($product);
        $minDate = $this->pitchBookingService->minBookableDate($product);
        $maxDate = $this->pitchBookingService->maxBookableDate($product);

        $location = $product->locations[0] ?? [];

        return Inertia::render('Booking::FrontendProductShow', $this->getData([
            'title' => $product->translateAttribute('name', config('app.locale')),
            'allowedDatesRouteName' => $this->pitchBookingService->allowedDatesRouteName(),
            'availableTimesRouteName' => $this->pitchBookingService->availableTimesRouteName(),
            'event' => $this->pitchBookingService->detailResource($product),
            'maxDate' => $maxDate?->toDateString(),
            'minDate' => $minDate?->toDateString(),
            'product' => $this->productService->productDetailResource($product),
            'timezone' => $schedule->timezone,
            'canBook' => $canBook,
            'noEventsMessage' => $canBook
                ? null
                : __('No timeslots available for booking at this time.'),
            'bookedEvents' => $this->pitchListingService->getBookedEventsForPitch($product),
            'mapPosition' => [
                'latitude' => $location['latitude'] ?? null,
                'longitude' => $location['longitude'] ?? null,
            ],
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
        $schedule = $product->eventSchedule;

        if (! $schedule) {
            return collect();
        }

        $date = Carbon::parse($dateTime);

        if (! $this->pitchBookingService->isDateWithinPitchWindow($product, $date->toDateString())) {
            return collect();
        }

        $pitchEnd = $product->getMeta('pitch_ended_at');
        $pitchStart = $product->getMeta('pitch_started_at');

        if ($pitchStart && $date->lt(Carbon::parse($pitchStart)->startOfDay())) {
            return collect();
        }

        if ($pitchEnd && $date->gt(Carbon::parse($pitchEnd)->endOfDay())) {
            return collect();
        }

        return $this->eventService->availableTimes($schedule, $dateTime);
    }

    public function allowedDates(Request $request, Product $product, string $month, string $year)
    {
        $schedule = $product->eventSchedule;

        if (! $schedule) {
            return collect();
        }

        $allowedDates = $this->eventService->allowedDates(
            $schedule,
            $month,
            $year
        );

        $pitchStart = $product->getMeta('pitch_started_at');
        $pitchEnd = $product->getMeta('pitch_ended_at');

        return $allowedDates->filter(function ($date) use ($pitchStart, $pitchEnd, $product) {
            if (! $this->pitchBookingService->isDateWithinPitchWindow($product, $date)) {
                return false;
            }

            if ($pitchStart && $date < Carbon::parse($pitchStart)->toDateString()) {
                return false;
            }

            if ($pitchEnd && $date > Carbon::parse($pitchEnd)->toDateString()) {
                return false;
            }

            return true;
        })->values();
    }
}
