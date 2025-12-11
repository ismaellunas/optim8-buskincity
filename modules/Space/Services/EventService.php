<?php

namespace Modules\Space\Services;

use App\Enums\PublishingStatus;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Modules\Space\Entities\Space;
use Modules\Space\Entities\SpaceEvent;

class EventService
{
    public function getRecords(
        Space $space,
        ?string $term = null,
        int $perPage = 10
    ): AbstractPaginator {
        return SpaceEvent::orderBy('started_at', 'ASC')
            ->select([
                'id',
                'title',
                'started_at',
                'ended_at',
                'status',
            ])
            ->hasSpace($space->id)
            ->when($term, function ($query, $term) {
                $query->search($term);
            })
            ->orderBy('started_at')
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function transformRecords(AbstractPaginator $records)
    {
        $dateFormat = config('constants.format.date_time_minute');

        $records->transform(function ($event) use ($dateFormat) {
            return [
                ...$event->only([
                    'id',
                    'title',
                    'status',
                ]),
                ...[
                    'started_at' => $event->started_at->format($dateFormat),
                    'ended_at' => $event->ended_at->format($dateFormat),
                    'display_status' => $event->displayStatus,
                ]
            ];
        });
    }

    public function getEditableRecord(SpaceEvent $event)
    {
        $event->load('translation');
        $event->load('city');

        return [
            ...$event->only([
                'id',
                'address',
                'city',
                'city_id',
                'country_code',
                'latitude',
                'longitude',
                'title',
                'timezone',
                'is_same_address_as_parent',
                'status',
            ]),
            ...[
                'ended_at' => $event->ended_at->toIso8601String(),
                'started_at' => $event->started_at->toIso8601String(),
                'translations' => $event->getTranslationsArray(),
                'city_relation' => $event->city,
            ]
        ];
    }

    public function createEvent($space, $inputs)
    {
        $event = new SpaceEvent();
        $event->space_id = $space->id;

        $this->updateEvent($event, $inputs);

        return $event;
    }

    public function updateEvent(&$event, $inputs)
    {
        $event->title = Arr::get($inputs, 'title');
        $event->started_at = Arr::get($inputs, 'started_at');
        $event->ended_at = Arr::get($inputs, 'ended_at');
        $event->fill(Arr::get($inputs, 'translations', []));
        $event->timezone = Arr::get($inputs, 'timezone');
        $event->is_same_address_as_parent = Arr::get($inputs, 'is_same_address_as_parent', true);
        $event->status = Arr::get($inputs, 'status', PublishingStatus::DRAFT->value);

        if ($event->is_same_address_as_parent) {
            $event->address = null;
            $event->city = null;
            $event->city_id = null;
            $event->country_code = null;
            $event->latitude = null;
            $event->longitude = null;
        } else {
            $event->address = Arr::get($inputs, 'address');
            $event->city = Arr::get($inputs, 'city');
            $event->city_id = Arr::get($inputs, 'city_id');
            $event->country_code = Arr::get($inputs, 'country_code');
            $event->latitude = Arr::get($inputs, 'latitude');
            $event->longitude = Arr::get($inputs, 'longitude');
        }

        return $event->save();
    }
}
