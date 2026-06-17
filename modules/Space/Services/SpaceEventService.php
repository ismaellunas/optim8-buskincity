<?php

namespace Modules\Space\Services;

use App\Models\User;
use App\Helpers\GoogleMap;
use App\Services\UserScopeService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
use Modules\Booking\Entities\Event as BookingEvent;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Enums\ProductStatus;
use Modules\Space\Entities\Space;
use Modules\Space\Entities\SpaceEvent;

class SpaceEventService
{
    private $cacheSpaceOptions;

    private function scopeRecords(
        Builder $query,
        Space $space,
        ?array $scopes = null
    ) {
        $today = Carbon::today('UTC');

        $query->whereHas('space', function (Builder $query) use ($space) {
            $query->whereDescendantOrSelf($space);
        })
        ->when($scopes, function ($query, $scopes) {
            foreach ($scopes as $scopeName => $value) {
                $query->$scopeName($value);
            }
        })
        ->whereDate('ended_at', '>=', $today->toDateString());
    }

    public function getSpaceEventRecords(
        Space $space,
        ?array $scopes = null,
        $perPage = 5
    ): LengthAwarePaginator {
        $space->loadMissing(['product.eventSchedule']);

        if ($space->isLeaf() && $space->product?->eventSchedule) {
            return $this->getBookedPitchEventRecords($space, $space->product, $scopes, $perPage);
        }

        // City pages are often leaf nodes when the pitch space is a sibling (linked only
        // via product.city_id). Match products by city_id / descendants, not tree depth.
        if ($this->hasPitchProductsUnderSpace($space)) {
            // City pages drill down: when the city has selectable pitch spaces,
            // list pitches first and only return events for the chosen pitch.
            // Skipping the guard when there are no pitch spaces preserves the
            // legacy "all city bookings" view for city pages with product-only
            // pitches (no Pitch-type Space row).
            $isCityPage = ($space->type?->name ?? null) === 'City';

            if (
                $isCityPage
                && empty($scopes['hasSpace'])
                && $this->pitchSpaceIdsForContext($space)->isNotEmpty()
            ) {
                return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
            }

            return $this->getAggregatedBookedPitchEventRecords($space, $scopes, $perPage);
        }

        $spaceEvents = SpaceEvent::published()
            ->where(function ($query) use ($space, $scopes) {
                $this->scopeRecords($query, $space, $scopes);
            })
            ->with([
                'space' => function ($query) {
                    $query->select([
                        'id', 'page_id', '_lft', '_rgt', 'parent_id', 'is_page_enabled', 'type_id', 'address', 'latitude', 'longitude', 'updated_at', 'city', 'country_code'
                    ]);
                    $query->withStructuredUrl();
                },
                'translations' => function ($query) {
                    $query->select('id', 'description', 'locale', 'space_event_id');
                },
            ])
            ->orderBy('started_at')
            ->paginate($perPage);

        $spaceEvents->getCollection()->transform(function ($event) {
            $space = $event->space;
            $purifiedConfigs = [
                'AutoFormat.AutoParagraph' => false,
            ];

            $shortDescription = nl2br(preg_replace("/[\r\n]+/", "\n", Purifier::clean(
                !empty($event->excerpt)
                ? $event->excerpt
                : Str::words($event->description ?? '', 60, ' ...')
            , $purifiedConfigs)));

            $data = [
                'id' => $event->id,
                'started_at' => $event->started_at->format('d M Y H:i'),
                'ended_at' => $event->ended_at->format('d M Y H:i'),
                'title' => $event->title,
                'short_description' => $shortDescription,
                'description' => nl2br(Purifier::clean($event->description, $purifiedConfigs)),
                'space_name' => $space->name,
                'space_url' => $space->pageLocalizeURL(currentLocale()),
                'address' => $event->fullAddress,
            ];

            if ($space->latitude && $space->longitude) {
                $data['direction_url'] = GoogleMap::directionUrl(
                    $space->latitude,
                    $space->longitude
                );
            }

            return $data;
        });

        return $spaceEvents;
    }

    private function getBookedPitchEventRecords(
        Space $space,
        Product $product,
        ?array $scopes = null,
        int $perPage = 5
    ): LengthAwarePaginator {
        $schedule = $product->eventSchedule;

        $query = $schedule->events()
            ->blockingAvailability()
            ->where('booked_at', '>=', now()->startOfDay())
            ->with(['orderLine.order.user', 'schedule']);

        if (! empty($scopes['dateRange'] ?? null)) {
            $query->dateRange($scopes['dateRange']);
        }

        $records = $query
            ->orderBy('booked_at')
            ->paginate($perPage);

        $records->getCollection()->transform(
            fn ($event) => $this->transformBookedPitchEvent($event, $space)
        );

        return $records;
    }

    private function getAggregatedBookedPitchEventRecords(
        Space $space,
        ?array $scopes,
        int $perPage
    ): LengthAwarePaginator {
        $query = $this->bookedPitchEventsQuery($space, $scopes)
            ->with([
                'orderLine.order.user',
                'schedule.schedulable' => function ($query) {
                    $query->with(['metas' => function ($metaQuery) {
                        $metaQuery->where('key', 'locations');
                    }]);
                },
            ]);

        $records = $query
            ->orderBy('booked_at')
            ->paginate($perPage);

        $records->getCollection()->transform(
            fn ($event) => $this->transformBookedPitchEvent($event, $space)
        );

        return $records;
    }

    private function bookedPitchEventsQuery(Space $space, ?array $scopes = null): Builder
    {
        $query = BookingEvent::query()
            ->blockingAvailability()
            ->where('booked_at', '>=', now()->startOfDay())
            ->whereHas('schedule', function (Builder $scheduleQuery) use ($space, $scopes) {
                $scheduleQuery->whereHasMorph(
                    'schedulable',
                    [Product::class],
                    function (Builder $productQuery) use ($space, $scopes) {
                        $productQuery
                            ->where('status', ProductStatus::PUBLISHED->value)
                            ->where($this->pitchProductsUnderSpaceConstraint($space));

                        if (! empty($scopes['hasSpace'])) {
                            $filterId = (int) $scopes['hasSpace'];

                            $productQuery->where(function (Builder $pitchQuery) use ($filterId) {
                                $pitchQuery
                                    ->where($pitchQuery->qualifyColumn('id'), $filterId)
                                    ->orWhere(function (Builder $linkedQuery) use ($filterId) {
                                        $linkedQuery
                                            ->where('productable_type', Space::class)
                                            ->where('productable_id', $filterId);
                                    });
                            });
                        }
                    }
                );
            });

        if (! empty($scopes['dateRange'] ?? null)) {
            $query->dateRange($scopes['dateRange']);
        }

        return $query;
    }

    /**
     * Match pitch products linked to descendant pitch spaces or the city FK.
     */
    private function pitchProductsUnderSpaceConstraint(Space $space): \Closure
    {
        return function (Builder $query) use ($space) {
            $pitchSpaceIds = $this->descendantPitchSpaceIds($space);

            $query->where(function (Builder $productQuery) use ($space, $pitchSpaceIds) {
                if ($pitchSpaceIds->isNotEmpty()) {
                    $productQuery->where(function (Builder $linkedQuery) use ($pitchSpaceIds) {
                        $linkedQuery
                            ->where('productable_type', Space::class)
                            ->whereIn('productable_id', $pitchSpaceIds);
                    });
                }

                if ($space->city_id) {
                    $method = $pitchSpaceIds->isNotEmpty() ? 'orWhere' : 'where';
                    $productQuery->$method('city_id', $space->city_id);
                }

                if ($pitchSpaceIds->isEmpty() && ! $space->city_id) {
                    $productQuery->whereRaw('1 = 0');
                }
            });
        };
    }

    public function hasPitchProductsUnderSpace(Space $space): bool
    {
        return Product::query()
            ->where('status', ProductStatus::PUBLISHED->value)
            ->whereHas('eventSchedule')
            ->where($this->pitchProductsUnderSpaceConstraint($space))
            ->exists();
    }

    /**
     * Booked performer performances for the admin space Events tab (FR-BOOK-4).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getAdminBookedPitchEventRows(Space $space, ?string $term = null): Collection
    {
        $space->loadMissing(['product.eventSchedule']);

        $query = $this->adminBookedPitchEventsQuery($space);

        if (! $query) {
            return collect();
        }

        if ($term) {
            $query->where(function (Builder $builder) use ($term) {
                $builder->whereHas('orderLine.order.user', function (Builder $userQuery) use ($term) {
                    $userQuery->search($term);
                })->orWhereHas('schedule', function (Builder $scheduleQuery) use ($term) {
                    $scheduleQuery->whereHasMorph(
                        'schedulable',
                        [Product::class],
                        fn (Builder $productQuery) => $productQuery->searchWithoutScout($term)
                    );
                });
            });
        }

        return $query
            ->with([
                'orderLine.order.user',
                'schedule.schedulable',
            ])
            ->orderBy('booked_at')
            ->get()
            ->map(fn (BookingEvent $event) => $this->transformAdminBookedPitchEvent($event, $space));
    }

    /**
     * Read-only booking list for scoped City / Special Events admins (nav "Booking").
     */
    public function getScopedAdminBookingRecords(
        User $user,
        ?string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $cityIds = app(UserScopeService::class)->scopedCityIds($user);

        if ($cityIds === []) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $query = BookingEvent::query()
            ->blockingAvailability()
            ->where('booked_at', '>=', now()->startOfDay())
            ->whereHas('schedule', function (Builder $scheduleQuery) use ($cityIds, $term) {
                $scheduleQuery->whereHasMorph(
                    'schedulable',
                    [Product::class],
                    function (Builder $productQuery) use ($cityIds, $term) {
                        $productQuery
                            ->where('status', ProductStatus::PUBLISHED->value)
                            ->whereIn('city_id', $cityIds);

                        if ($term) {
                            $productQuery->searchWithoutScout($term);
                        }
                    }
                );
            });

        if ($term) {
            $query->where(function (Builder $builder) use ($term) {
                $builder->whereHas('orderLine.order.user', function (Builder $userQuery) use ($term) {
                    $userQuery->search($term);
                });
            });
        }

        $records = $query
            ->with([
                'orderLine.order.user',
                'schedule.schedulable',
            ])
            ->orderBy('booked_at')
            ->paginate($perPage);

        $records->getCollection()->transform(
            fn (BookingEvent $event) => $this->transformScopedAdminBookingRow($event)
        );

        return $records;
    }

    private function transformScopedAdminBookingRow(BookingEvent $event): array
    {
        $product = $event->schedule?->schedulable;
        $pitchSpace = $product instanceof Product
            ? $this->resolvePitchSpaceForProduct($product)
            : null;
        $performer = $event->orderLine?->order?->user;
        $dateFormat = config('constants.format.date_time_minute');
        $status = Str::title((string) $event->status);

        return [
            'id' => $event->id,
            'performer' => $performer?->full_name ?: '—',
            'pitch' => $pitchSpace?->name ?? $product?->displayName ?? '—',
            'started_at' => $event->timezonedBookedAt->format($dateFormat),
            'ended_at' => $event->endedTime->format($dateFormat),
            'status' => $status,
            'status_class' => in_array($event->status, ['upcoming', 'ongoing'], true)
                ? 'success'
                : null,
        ];
    }

    private function adminBookedPitchEventsQuery(Space $space): ?Builder
    {
        if ($space->isLeaf() && $space->product?->eventSchedule) {
            return $space->product->eventSchedule->events()
                ->blockingAvailability()
                ->where('booked_at', '>=', now()->startOfDay());
        }

        if ($this->hasPitchProductsUnderSpace($space)) {
            return $this->bookedPitchEventsQuery($space);
        }

        return null;
    }

    private function transformAdminBookedPitchEvent(BookingEvent $event, Space $contextSpace): array
    {
        $product = $event->schedule?->schedulable;
        $pitchSpace = $product ? $this->resolvePitchSpaceForProduct($product) : null;
        $performer = $event->orderLine?->order?->user;
        $order = $event->orderLine?->order;
        $dateFormat = config('constants.format.date_time_minute');

        return [
            'id' => $event->id,
            'record_type' => 'booked',
            'title' => $performer?->full_name ?: ($product?->displayName ?? __('Booked performance')),
            'pitch_name' => $pitchSpace?->name ?? $product?->displayName,
            'started_at' => $event->timezonedBookedAt->format($dateFormat),
            'ended_at' => $event->endedTime->format($dateFormat),
            'status' => $event->status,
            'display_status' => Str::title((string) $event->status),
            'sort_at' => $event->booked_at,
            'order_id' => $order?->id,
            'can_reschedule' => $order ? Gate::check('rescheduleBooking', $order) : false,
            'reschedule_url' => $order
                ? route('admin.booking.orders.reschedule', $order->id)
                : null,
            'space_name' => $pitchSpace?->name ?? $contextSpace->name,
        ];
    }

    private function descendantPitchSpaceIds(Space $space): Collection
    {
        $pitchTypeIds = app(SpaceService::class)->types()
            ->whereIn('name', ['Pitch', 'Special Events / Festivals'])
            ->pluck('id');

        if ($pitchTypeIds->isEmpty()) {
            return collect();
        }

        return Space::whereDescendantOf($space)
            ->whereIsLeaf()
            ->whereIn('type_id', $pitchTypeIds)
            ->pluck('id');
    }

    /**
     * Pitch space IDs visible from a context space (descendants + same-city siblings).
     */
    private function pitchSpaceIdsForContext(Space $space): Collection
    {
        return app(SpaceService::class)
            ->pitchSpacesForContextQuery($space)
            ->pluck('id');
    }

    private function transformBookedPitchEvent(BookingEvent $event, Space $contextSpace): array
    {
        $product = $event->schedule?->schedulable;
        $pitchSpace = $product ? $this->resolvePitchSpaceForProduct($product) : null;
        $displaySpace = $pitchSpace ?? $contextSpace;
        $user = $event->orderLine?->order?->user;
        $location = $product?->locations[0] ?? [];

        $latitude = $displaySpace->latitude ?? ($location['latitude'] ?? null);
        $longitude = $displaySpace->longitude ?? ($location['longitude'] ?? null);

        $data = [
            'id' => 'booked-'.$event->id,
            'started_at' => $event->timezonedBookedAt->format('d M Y H:i'),
            'ended_at' => $event->endedTime->format('d M Y H:i'),
            'title' => $user?->full_name ?: ($product?->displayName ?? __('Booked performance')),
            'short_description' => '',
            'description' => '',
            'space_name' => $pitchSpace?->name ?? ($product?->displayName ?? $displaySpace->name),
            'space_url' => $pitchSpace?->hasEnabledPage()
                ? $pitchSpace->pageLocalizeURL(currentLocale())
                : '',
            'address' => $displaySpace->address ?? ($location['address'] ?? ''),
        ];

        if ($latitude && $longitude) {
            $data['direction_url'] = GoogleMap::directionUrl($latitude, $longitude);
        }

        return $data;
    }

    private function resolvePitchSpaceForProduct(Product $product): ?Space
    {
        if ($product->productable_type !== Space::class || ! $product->productable_id) {
            return null;
        }

        return Space::find($product->productable_id);
    }

    /**
     * Valid pitch filter IDs for the public events API `space` query param.
     *
     * City pages list pitch Products (city_id), while drill-down may use a
     * sibling Pitch Space id — both must be accepted when filtering events.
     *
     * @return Collection<int, int>
     */
    public function getPitchFilterOptionIds(Space $space): Collection
    {
        $ids = $this->pitchSpaceIdsForContext($space);

        if (($space->type?->name ?? null) === 'City' && $space->city_id) {
            $productIds = Product::query()
                ->where('status', ProductStatus::PUBLISHED->value)
                ->where('city_id', $space->city_id)
                ->whereHas('eventSchedule')
                ->pluck('id');

            $ids = $ids->merge($productIds);
        }

        return $ids->unique()->values();
    }

    public function getSpaceRecordOptions(
        Space $space,
        string $noneLabel = null
    ): Collection {
        if (is_null($this->cacheSpaceOptions)) {
            $pitchSpaces = app(SpaceService::class)
                ->pitchSpacesForContextQuery($space)
                ->withDepth()
                ->orderBy('name')
                ->get(['id', 'name']);

            $spaceEventSpaces = SpaceEvent::query()
                ->where(function ($query) use ($space) {
                    $this->scopeRecords($query, $space);
                })
                ->with([
                    'space' => function ($query) {
                        $query->select(['id', 'name']);
                        $query->withDepth();
                    },
                ])
                ->get(['id', 'space_id'])
                ->pluck('space');

            $this->cacheSpaceOptions = $pitchSpaces
                ->concat($spaceEventSpaces)
                ->unique('id')
                ->sortBy([
                    ['depth', 'asc'],
                    ['name', 'asc'],
                ])
                ->values();
        }

        $options = $this->cacheSpaceOptions->map(fn ($space) => [
            'id' => $space->id,
            'value' => $space->name,
            'depth' => $space->depth ?? 0,
        ]);

        if ($noneLabel) {
            $options->prepend([
                'id' => null,
                'value' => $noneLabel,
            ]);
        }

        return $options->values();
    }
}
