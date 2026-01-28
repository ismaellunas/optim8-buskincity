<?php

namespace Modules\Booking\Http\Controllers\Frontend;

use App\Enums\PublishingStatus;
use App\Http\Controllers\CrudController;
use App\Services\SettingService;
use Carbon\Carbon;
use Inertia\Inertia;
use Modules\Booking\Entities\ProductEvent;
use Modules\Booking\Services\EventService;
use Modules\Ecommerce\Services\ProductService;

class EventController extends CrudController
{
    protected $title = 'Event Booking';
    protected $baseRouteName = 'booking.events';

    public function __construct(
        private EventService $eventService,
        private ProductService $productService
    ) {}

    public function show(ProductEvent $event)
    {
        if ($event->status !== PublishingStatus::PUBLISHED->value) {
            abort(404);
        }

        $product = $event->product;
        $schedule = $this->getEffectiveSchedule($event);

        if (!$schedule) {
            abort(404, 'Event schedule not configured');
        }

        $minDate = $event->started_at;
        $maxDate = $event->ended_at;

        $mapPosition = [
            'latitude' => $event->latitude,
            'longitude' => $event->longitude,
        ];

        $translation = $event->translateOrDefault(config('app.locale'));

        return Inertia::render('Booking::FrontendEventShow', $this->getData([
            'title' => $event->title,
            'allowedDatesRouteName' => 'booking.events.allowed-dates',
            'availableTimesRouteName' => 'booking.events.available-times',
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $translation?->description,
                'excerpt' => $translation?->excerpt,
                'started_at' => $event->started_at->toIso8601String(),
                'ended_at' => $event->ended_at->toIso8601String(),
                'duration' => $product?->duration ? $product->duration.' minutes' : null,
                'timezone' => $event->timezone,
                'location' => [
                    'address' => $event->address,
                    'city' => $event->city,
                    'country_code' => $event->country_code,
                    'latitude' => $event->latitude,
                    'longitude' => $event->longitude,
                ],
            ],
            'product' => $product ? $this->productService->productDetailResource($product) : null,
            'pitchName' => $product?->translateAttribute('name', config('app.locale')),
            'maxDate' => $maxDate->toDateString(),
            'minDate' => $minDate->toDateString(),
            'mapPosition' => $mapPosition,
            'timezone' => $schedule->timezone,
            'googleApiKey' => app(SettingService::class)->getGoogleApi(),
            'i18n' => [
                'events' => __('booking_module::terms.events'),
                'event_booking' => __('Event booking'),
                'booking_event_confirmation' => __('Booking event confirmation'),
            ],
        ]));
    }

    public function allowedDates(ProductEvent $event, string $month, string $year)
    {
        $schedule = $this->getEffectiveSchedule($event);

        if ($event->status !== PublishingStatus::PUBLISHED->value || !$schedule) {
            return collect();
        }

        $allowedDates = $this->eventService->allowedDates(
            $schedule,
            $month,
            $year
        );

        $startDate = $event->started_at->toDateString();
        $endDate = $event->ended_at->toDateString();

        return $allowedDates->filter(function ($date) use ($startDate, $endDate) {
            return $date >= $startDate && $date <= $endDate;
        })->values();
    }

    public function availableTimes(ProductEvent $event, string $dateTime)
    {
        $schedule = $this->getEffectiveSchedule($event);

        if ($event->status !== PublishingStatus::PUBLISHED->value || !$schedule) {
            return collect();
        }

        $date = Carbon::parse($dateTime);
        if ($date->lt($event->started_at) || $date->gt($event->ended_at)) {
            return collect();
        }

        return $this->eventService->availableTimes($schedule, $dateTime);
    }

    private function getEffectiveSchedule(ProductEvent $event): ?\Modules\Booking\Entities\Schedule
    {
        $eventSchedule = $event->schedule;
        $productSchedule = $event->product?->eventSchedule;

        if ($eventSchedule && $this->scheduleHasAvailability($eventSchedule)) {
            return $eventSchedule;
        }

        if ($productSchedule) {
            return $productSchedule;
        }

        return $eventSchedule;
    }

    private function scheduleHasAvailability(\Modules\Booking\Entities\Schedule $schedule): bool
    {
        $weeklyHasTimes = $schedule->weeklyHours
            ->where('is_available', true)
            ->filter(fn ($rule) => $rule->times->isNotEmpty())
            ->isNotEmpty();

        $overrideHasTimes = $schedule->dateOverrides
            ->where('is_available', true)
            ->filter(fn ($rule) => $rule->times->isNotEmpty())
            ->isNotEmpty();

        return $weeklyHasTimes || $overrideHasTimes;
    }
}
