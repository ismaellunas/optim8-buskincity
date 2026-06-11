<?php

namespace Modules\Booking\Services;

use App\Models\User;
use App\Services\CountryService;
use Carbon\Carbon;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Enums\ProductStatus;

class PitchListingService
{
    public function __construct(
        private PitchBookingService $pitchBookingService,
        private EventService $eventService,
    ) {}

    public function getFrontendRecords(
        User $user,
        ?string $term = null,
        array $scopes = [],
        int $perPage = 15
    ): AbstractPaginator {
        $locale = config('app.locale');

        $records = Product::query()
            ->where('status', ProductStatus::PUBLISHED->value)
            ->with(['eventSchedule', 'city'])
            ->when($term, fn ($query) => $query->searchWithoutScout($term))
            ->orderByRaw("attribute_data->'name'->'value'->>? ASC", [$locale])
            ->get()
            ->filter(fn (Product $product) => $this->performerCanViewPitch($user, $product))
            ->filter(fn (Product $product) => $this->isPitchInListingWindow($product))
            ->when($scopes['city'] ?? null, function (Collection $products, string $city) {
                return $products->filter(fn (Product $product) => $product->display_city === $city);
            })
            ->when($scopes['country'] ?? null, function (Collection $products, string $country) {
                return $products->filter(fn (Product $product) => ($product->locations[0]['country_code'] ?? null) === $country);
            })
            ->values();

        $page = (int) request()->get('page', 1);
        $total = $records->count();
        $items = $records->slice(($page - 1) * $perPage, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function transformFrontendRecords(AbstractPaginator $records): void
    {
        $locale = config('app.locale');

        $records->setCollection(
            $records->getCollection()->map(function (Product $product) use ($locale) {
                $pitchStart = $product->getMeta('pitch_started_at');
                $pitchEnd = $product->getMeta('pitch_ended_at');

                return [
                    'id' => $product->id,
                    'title' => $product->translateAttribute('name', $locale),
                    'pitch_name' => $product->translateAttribute('name', $locale),
                    'description' => $product->translateAttribute('description', $locale),
                    'excerpt' => $product->translateAttribute('short_description', $locale),
                    'started_at' => $pitchStart ? Carbon::parse($pitchStart)->format('M d, Y') : '',
                    'ended_at' => $pitchEnd ? Carbon::parse($pitchEnd)->format('M d, Y') : '',
                    'city' => $product->display_city,
                    'country' => $product->locations[0]['country_code'] ?? '',
                    'can_book' => $this->hasAvailableTimeslot($product),
                ];
            })
        );
    }

    public function getFrontendCountryOptions(User $user): array
    {
        return $this->publishedPitchesForUser($user)
            ->map(fn (Product $product) => $product->locations[0]['country_code'] ?? null)
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $code) => [
                'value' => $code,
                'name' => app(CountryService::class)->getCountryName($code),
            ])
            ->all();
    }

    public function getFrontendCityOptions(User $user): array
    {
        return $this->publishedPitchesForUser($user)
            ->filter(fn (Product $product) => ! empty($product->display_city))
            ->unique(fn (Product $product) => $product->display_city.'|'.($product->locations[0]['country_code'] ?? ''))
            ->sortBy(fn (Product $product) => $product->display_city)
            ->values()
            ->map(fn (Product $product) => [
                'value' => $product->display_city,
                'name' => $product->display_city,
                'country_code' => $product->locations[0]['country_code'] ?? null,
            ])
            ->all();
    }

    public function hasAvailableTimeslot(Product $product): bool
    {
        $schedule = $product->eventSchedule;

        if (! $schedule) {
            return false;
        }

        $minDate = $this->pitchBookingService->minBookableDate($product);
        $maxDate = $this->pitchBookingService->maxBookableDate($product);

        if (! $minDate || ! $maxDate || $minDate->gt($maxDate)) {
            return false;
        }

        $scanUntil = $minDate->copy()->addDays(min(30, $minDate->diffInDays($maxDate)));

        if ($scanUntil->gt($maxDate)) {
            $scanUntil = $maxDate->copy();
        }

        for ($date = $minDate->copy(); $date->lte($scanUntil); $date->addDay()) {
            if (! $this->pitchBookingService->isDateWithinPitchWindow($product, $date->toDateString())) {
                continue;
            }

            $times = $this->eventService->availableTimes($schedule, $date->toDateString());

            if ($times->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }

    public function getBookedEventsForPitch(Product $product): array
    {
        $schedule = $product->eventSchedule;

        if (! $schedule) {
            return [];
        }

        return $schedule->events()
            ->blockingAvailability()
            ->where('booked_at', '>=', now()->startOfDay())
            ->with(['orderLine.order.user', 'schedule'])
            ->orderBy('booked_at')
            ->get()
            ->map(fn ($event) => [
                'id' => $event->id,
                'date' => $event->booked_at->toDateString(),
                'time' => $event->booked_at->format('H:i'),
                'started_at' => $event->timezonedBookedAt->format('d M Y H:i'),
                'ended_at' => $event->endedTime->format('d M Y H:i'),
                'title' => $this->performerDisplayName($event),
            ])
            ->all();
    }

    private function performerDisplayName($event): string
    {
        $user = $event->orderLine?->order?->user;

        if (! $user) {
            return __('Booked performance');
        }

        return $user->full_name ?: __('Booked performance');
    }

    private function publishedPitchesForUser(User $user): Collection
    {
        return Product::query()
            ->where('status', ProductStatus::PUBLISHED->value)
            ->with(['eventSchedule', 'city'])
            ->get()
            ->filter(fn (Product $product) => $this->performerCanViewPitch($user, $product))
            ->filter(fn (Product $product) => $this->isPitchInListingWindow($product))
            ->values();
    }

    private function performerCanViewPitch(User $user, Product $product): bool
    {
        return $user->hasRole($product->roles ?? []);
    }

    private function isPitchInListingWindow(Product $product): bool
    {
        $pitchEnd = $product->getMeta('pitch_ended_at');

        if ($pitchEnd && Carbon::parse($pitchEnd)->endOfDay()->lt(today())) {
            return false;
        }

        $pitchStart = $product->getMeta('pitch_started_at');

        if ($pitchStart && Carbon::parse($pitchStart)->startOfDay()->gt(today()->addYear())) {
            return false;
        }

        return (bool) $product->eventSchedule;
    }
}
