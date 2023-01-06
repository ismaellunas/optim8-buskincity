<?php

namespace Modules\Booking\Services;

use App\Models\Country;
use App\Services\IPService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use KMLaravel\GeographicalCalculator\Classes\Geo;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\Product;

class EventsCalendarService
{
    private $geo;

    private function getGeo(): Geo
    {
        if (is_null($this->geo)) {
            $this->geo = new Geo();
        }

        return $this->geo->clearResult();
    }

    private function defaultZoom(): int
    {
        return 8;
    }

    private function minZoom(): int
    {
        return 3;
    }

    private function maxZoom(): int
    {
        return 20;
    }

    public function getRecords(
        int $perPage = 15,
        array $scopes = []
    ) {
        $records = Order::when($scopes, function ($query, $scopes) {
            foreach ($scopes as $scopeName => $value) {
                $query->when($value, function ($query, $value) use ($scopeName) {
                    if ($scopeName == 'dateRange') {
                        $query->whereHas(
                            'firstEventLine.latestEvent',
                            function (Builder $query) use ($scopeName, $value) {
                                $query->$scopeName($value);
                            }
                        );
                    } elseif ($scopeName == 'city') {
                        $query->whereHas('firstEventLine.purchasable.product.metas', function (Builder $query) use ($value) {
                            $query->where('key', 'locations');
                            $query->where(DB::raw("value::json->0->>'city'"), $value);
                        });
                    } elseif ($scopeName == 'country') {
                        $query->whereHas('firstEventLine.purchasable.product.metas', function (Builder $query) use ($value) {
                            $query->where('key', 'locations');
                            $query->where(DB::raw("value::json#>>'{0,country_code}'"), $value);
                        });
                    } else {
                        $query->$scopeName($value);
                    }
                });
            }
        })
            ->with([
                'firstEventLine' => function ($query) {
                    $query->with([
                        'latestEvent' => function ($query) {
                            $query->with('schedule', function ($query) {
                                $query->select('id', 'timezone');
                            });
                        },
                        'purchasable' => function ($query) {
                            $query->with('product', function ($query) {
                                $query->select('id', 'product_type_id', 'attribute_data');
                                $query->with('metas', function ($query) {
                                    $query->whereIn('key', ['locations']);
                                });
                            });
                        },
                    ]);
                },
                'user' => function ($query) {
                    $query->select('id', 'email', 'first_name', 'last_name', 'unique_key');
                    $query->with([
                        'metas' => function ($query) {
                            $query->whereIn('key', ['stage_name']);
                        },
                        'profilePhoto' => function ($q) {
                            $q->select([
                                'id',
                                'extension',
                                'file_name',
                                'file_url',
                                'version',
                            ]);
                        },
                    ]);
                },
            ])
            ->select(
                'id',
                'user_id',
                'status',
                'placed_at',
            )
            ->paginate($perPage);

        $fromPosition = app(IPService::class)->getGeoLocation();

        $records->getCollection()->transform(function ($record) use ($fromPosition) {
            $event = $record->firstEventLine->latestEvent;

            $product = $record->firstEventLine->purchasable->product;

            $customer = $record->user;

            $location = $product->locations[0] ?? [];

            return (object) [
                'id' => $record->id,
                'product_name' => $product->displayName,
                'user' => [
                    'name' => $customer->fullName ?? null,
                    'stage_name' => $customer->metas->where('key', 'stage_name')->value('value'),
                    'profile_page_url' => $customer->profilePageUrl,
                    'profile_photo_url' => $customer->optimizedProfilePhotoUrl,
                ],
                'date' => $event->timezonedBookedAt->format('d M Y'),
                'timezone' => $event->timezonedBookedAt->format('P'),
                'event' => [
                    'date' => $event->timezonedBookedAt->format('d F Y'),
                    'duration' => $event->displayDuration,
                    'start_end_time' => $event->displayStartEndTime,
                    'timezone' => $event->schedule->timezone,
                ],
                'location' => $location,
                'direction_url' => app(ProductEventService::class)
                    ->getGoogleMapDirectionUrl($product, $fromPosition),
            ];
        });

        return $records;
    }

    public function getLocationOptions(): array
    {
        $products = Product::with(['metas'])
            ->whereHas('productType', function ($query) {
                $query->where('name', 'Event');
            })
            ->whereHas('variants', function ($query) {
                $query->whereHas('orderLine.order');
                $query->whereHas('orderLine.latestEvent');
            })
            ->whereHas('metas', function ($query) {
                $query->where('key', 'locations');
            })
            ->get(['id', 'product_type_id']);

        $locations = $products
            ->filter(fn ($product) => !empty($product->locations) && is_array($product->locations))
            ->mapToGroups(function ($product) {
                $location = $product->locations[0];
                return [$location['country_code'] => $location['city']];
            });

        $countries = collect();

        if ($locations->keys()->isNotEmpty()) {
            $countries = Country::
                whereIn('alpha2', $locations->keys())
                ->get([
                    'alpha2',
                    'display_name',
                ]);
        }

        return $locations->transform(function ($location, $key) use ($countries) {
            return [
                'country_code' => $key,
                'country' => $countries->where('alpha2', $key)->first()->display_name ?? '',
                'cities' => collect($location)
                    ->map(fn ($value) => trim($value))
                    ->filter()
                    ->unique()
                    ->all(),
            ];
        })->all();
    }

    public function getCoordinates(LengthAwarePaginator $pagination): array
    {
        return $pagination
            ->getCollection()
            ->map(function ($event) {
                $location = $event->location;

                if (!empty($location['latitude']) && !empty($location['longitude'])) {
                    return [$location['latitude'], $location['longitude']];
                }

                return null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->all();
    }

    public function getCenterCoordinate(
        array $coordinates,
        array $defaultCoordinate = []
    ): array {
        if (!empty($coordinates)) {
            return $this->getCenter($coordinates);
        }

        return $defaultCoordinate;
    }

    public function getCenter(array $points): array
    {
        return $this
            ->getGeo()
            ->setPoints($points)
            ->getCenter();
    }

    public function getFarthestPoint(array $mainPoint, array $points): array
    {
        return (array) collect(
            $this->getGeo()
                ->setMainPoint([$mainPoint['lat'], $mainPoint['long']])
                ->setPoints($points)
                ->getFarthest()
        )->first();
    }

    public function getFarthestDistance(array $mainPoint, array $farthestPoint): float
    {
        $distance = collect(
            $this->getGeo()
                ->setOptions(['units' => ['km']])
                ->setPoint([$mainPoint['lat'], $mainPoint['long']])
                ->setPoint([$farthestPoint[0], $farthestPoint[1]] )
                ->getDistance()
        )->first();

        return $distance['km'] ?? 0;
    }

    public function zoom($km): float
    {
        if ($km > 0) {
            $zoom = round(log(20000 / $km) / log(2), 0, PHP_ROUND_HALF_DOWN);

            if ($zoom < $this->minZoom()) {
                return (float) $this->minZoom();
            } elseif ($zoom > $this->maxZoom()) {
                return (float) $this->maxZoom();
            }

            return $zoom;
        }

        return (float) $this->defaultZoom();
    }
}
