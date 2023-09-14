<?php

namespace Modules\Space\Services;

use Illuminate\Pagination\AbstractPaginator;
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
            ->hasSpace($space->id)
            ->when($term, function ($query, $term) {
                $query->search($term);
            })
            ->paginate($perPage);
    }

    public function transformRecords(AbstractPaginator $records)
    {
        $dateFormat = config('constants.format.date_time_minute');

        $records->transform(function ($event) use ($dateFormat) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'started_at' => $event->started_at->format($dateFormat),
                'ended_at' => $event->ended_at->format($dateFormat),
            ];
        });
    }

    public function getEditableRecord($space, $event)
    {
        $event = SpaceEvent::hasSpace($space->id)
            ->with('translations')
            ->find($event->id);

        return [
            'id' => $event->id,
            'address' => $event->address,
            'city' => $event->city,
            'country_code' => $event->country_code,
            'latitude' => $event->latitude,
            'longitude' => $event->longitude,
            'ended_at' => $event->ended_at->toIso8601String(),
            'started_at' => $event->started_at->toIso8601String(),
            'title' => $event->title,
            'translations' => $event->getTranslationsArray(),
            'timezone' => $event->timezone,
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
        $event->title = $inputs['title'];
        $event->address = $inputs['address'];
        $event->city = $inputs['city'];
        $event->country_code = $inputs['country_code'];
        $event->latitude = $inputs['latitude'];
        $event->longitude = $inputs['longitude'];
        $event->started_at = $inputs['started_at'];
        $event->ended_at = $inputs['ended_at'];
        $event->fill($inputs['translations']);
        $event->timezone = $inputs['timezone'];

        return $event->save();
    }
}
