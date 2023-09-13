<?php

namespace Modules\Booking\Services;

use App\Models\User;
use App\Services\CountryService;
use App\Services\IPService;
use App\Services\ModuleService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use KMLaravel\GeographicalCalculator\Classes\Geo;
use Modules\Booking\Entities\Event;
use Modules\Booking\Entities\EventCalendar;
use Modules\Space\Entities\Space;

class EventsCalendarService
{
    private $geo;

    private Collection $cachedEvents;
    private Collection $cachedSpaces;
    private Collection $cachedUsers;

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

    private function availableTypes(): array
    {
        $moduleService = app(ModuleService::class);
        $types = [];

        if ($moduleService->isModuleActive('Booking')) {
            $types[] = 'booked_event';
        }

        if ($moduleService->isModuleActive('Space')) {
            $types[] = 'space_event';
        }

        return $types;
    }

    private function setCachedEvents($paginator)
    {
        $eventIds = $paginator
            ->getCollection()
            ->filter(function($record) {
                return Arr::has($record->entity_ids, 'event_id');
            })
            ->map(fn ($record) => $record->entity_ids['event_id'])
            ->all();

        $this->cachedEvents = Event::whereIn('id', $eventIds)->get();
    }

    private function setCachedSpaces($paginator)
    {
        $spaceIds = $paginator
            ->getCollection()
            ->filter(function($record) {
                return Arr::has($record->entity_ids, 'space_id');
            })
            ->map(fn ($record) => $record->entity_ids['space_id'])
            ->all();

        $this->cachedSpaces = Space::with([
                'page.translations' => function ($query) {
                    $query->select([
                        'id',
                        'page_id',
                        'locale',
                        'status',
                    ]);
                },
                'logoMedia' => function ($query) {
                    $query->select([
                        'media.id',
                        'media.extension',
                        'media.file_name',
                        'media.file_url',
                        'media.version',
                    ]);
                },
            ])
            ->whereIn('id', $spaceIds)
            ->get();
    }

    private function setCachedUsers($paginator)
    {
        $userIds = $paginator
            ->getCollection()
            ->filter(fn ($record) => $record->user_id)
            ->map(fn ($record) => $record->user_id)
            ->all();

        $this->cachedUsers = User::
            select([
                'id',
                'first_name',
                'last_name',
                'unique_key',
                'profile_photo_media_id'
            ])
            ->with(['profilePhoto' => function ($query) {
                $query->select([
                    'id',
                    'extension',
                    'file_name',
                    'file_url',
                    'version',
                ]);
            }])
            ->whereIn('id', $userIds)
            ->get();
    }

    public function getRecords(
        int $perPage = 15,
        array $scopes = []
    ) {
        $paginator = EventCalendar::
            when($scopes, function ($query, $scopes) {
                foreach ($scopes as $scopeName => $value) {
                    $query->when($value, function ($query, $value) use ($scopeName) {
                        $query->$scopeName($value);
                    });
                }
            })
            ->inType($this->availableTypes())
            ->orderBy('started_at')
            ->orderBy('ended_at')
            ->paginate($perPage);

        $fromPosition = app(IPService::class)->getGeoLocation();

        $this->setCachedEvents($paginator);
        $this->setCachedSpaces($paginator);
        $this->setCachedUsers($paginator);

        $paginator->through(function ($record) use ($fromPosition) {
            $method = Str::camel($record->type).'Record';

            return $this->$method($record, $fromPosition);
        });

        return $paginator;
    }

    private function bookedEventRecord($record, array $geoLocation): array
    {
        $event = $this->cachedEvents->firstWhere('id', $record->entity_ids['event_id']);
        $user = $this->cachedUsers->firstWhere('id', $record->user_id);

        $defaultData = $record->eventData();

        $data = [
            'title' => $record->title_alt ?? $record->title,
            'page_url' => $user->profilePageUrl,
            'photo_url' => $user->optimizedProfilePhotoUrl,
            'duration' => $event->displayDuration,
            'direction_url' => $record->directionUrl($geoLocation),
            'started_time' => $event->displayStartEndTime,
            'ended_datetime' => null,
            'ended_time' => null,
        ];

        return [
            ...$defaultData,
            ...$data,
        ];
    }

    private function spaceEventRecord($record, array $geoLocation): array
    {
        $space = $this->cachedSpaces->firstWhere('id', $record->entity_ids['space_id']);

        $pageTranslation = $space->page->translate(currentLocale(), true);

        $data = [
            'page_url' => (
                $space->is_page_enabled && $pageTranslation->isPublished
                ? $space->pageLocalizeURL(currentLocale())
                : ''
            ),
            'photo_url' => $space->getOptimizedLogoImageUrl(300, 300),
            'duration' => null,
            'direction_url' => $record->directionUrl($geoLocation),
        ];

        return [
            ...$record->eventData(),
            ...$data,
        ];
    }

    public function getLocationOptions(): array
    {
        $countryService = app(CountryService::class);

        $options = EventCalendar::select('country_code', 'city')
            ->whereNotNull('country_code')
            ->whereNotNull('city')
            ->inType($this->availableTypes())
            ->distinct()
            ->get()
            ->groupBy('country_code')
            ->map(function ($items, $key) use ($countryService) {
                return [
                    'country_code' => $key,
                    'country' => $countryService->getCountryName($key),
                    'cities' => $items->map(function ($item) {
                        return Str::title($item->city);
                    })->all(),
                ];
            })
            ->all();

        return $options;
    }

    public function getCoordinates(LengthAwarePaginator $pagination): array
    {
        return $pagination
            ->getCollection()
            ->map(function ($event) {
                $location = $event['geolocation'] ?? [];

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
            $center = $this->getCenter($coordinates);

            if (
                !empty($center)
                && array_key_exists('lat', $center)
                && array_key_exists('long', $center)
            ) {
                return [
                    'latitude' => $center['lat'],
                    'longitude' => $center['long'],
                ];
            }
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
                ->setMainPoint([$mainPoint['latitude'], $mainPoint['longitude']])
                ->setPoints($points)
                ->getFarthest()
        )->first();
    }

    public function getFarthestDistance(array $mainPoint, array $farthestPoint): float
    {
        $distance = collect(
            $this->getGeo()
                ->setOptions(['units' => ['km']])
                ->setPoint([$mainPoint['latitude'], $mainPoint['longitude']])
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
