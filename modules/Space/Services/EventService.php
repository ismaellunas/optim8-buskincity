<?php

namespace Modules\Space\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\AbstractPaginator;
use Modules\Event\Entities\Event;
use Modules\Space\Entities\Space;

class EventService
{
    public function getRecords(
        Space $space,
        ?string $term = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = Event::orderBy('started_at', 'ASC')
            ->whereHasMorph('eventable', Space::class, function ($query) use ($space) {
                $query->where('id', $space->id);
            })
            ->when($term, function ($query) use ($term) {
                $query->where('title', 'ILIKE', '%'.$term.'%');
            });

        return $query->paginate($perPage);
    }

    public function transformRecords(AbstractPaginator $records)
    {
        $records->getCollection()->transform(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'started_at' => $event->started_at->toDateTimeString(),
                'ended_at' => $event->ended_at->toDateTimeString(),
            ];
        });
    }

    public function getEditableRecord($space, $event)
    {
        $event = Event::
            whereHasMorph(
                'eventable',
                Space::class,
                function ($query) use ($space) {
                    $query->where('id', $space->id);
                }
            )
            ->with('translations')
            ->find($event->id);

        return [
            'id' => $event->id,
            'address' => $event->address,
            'ended_at' => $event->ended_at->toIso8601String(),
            'started_at' => $event->started_at->toIso8601String(),
            'title' => $event->title,
            'translations' => $event->getTranslationsArray(),
        ];
    }

    public function createEvent($space, $inputs)
    {
        $event = new Event();

        $this->updateEvent($event, $inputs);

        $space->events()->save($event);

        return $event;
    }

    public function updateEvent(&$event, $inputs)
    {
        $event->title = $inputs['title'];
        $event->address = $inputs['address'];
        $event->started_at = $inputs['started_at'];
        $event->ended_at = $inputs['ended_at'];
        $event->fill($inputs['translations']);

        return $event->save();
    }
}
