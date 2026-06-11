<?php

namespace Modules\Space\Services;

use App\Enums\PublishingStatus;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Modules\Space\Entities\Space;
use Modules\Space\Entities\SpaceEvent;

class EventService
{
    public function __construct(
        private SpaceEventService $spaceEventService,
    ) {}

    public function getRecords(
        Space $space,
        ?string $term = null,
        int $perPage = 10
    ): AbstractPaginator {
        $dateFormat = config('constants.format.date_time_minute');

        $cmsRows = SpaceEvent::query()
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
            ->get()
            ->map(fn (SpaceEvent $event) => [
                'id' => $event->id,
                'record_type' => 'cms',
                'title' => $event->title,
                'pitch_name' => null,
                'started_at' => $event->started_at->format($dateFormat),
                'ended_at' => $event->ended_at->format($dateFormat),
                'status' => $event->status,
                'display_status' => $event->displayStatus,
                'sort_at' => $event->started_at,
                'can_reschedule' => false,
                'reschedule_url' => null,
                'order_id' => null,
            ]);

        $bookedRows = $this->spaceEventService->getAdminBookedPitchEventRows($space, $term);

        $merged = $cmsRows
            ->concat($bookedRows)
            ->sortBy('sort_at')
            ->values();

        return $this->paginateCollection($merged, $perPage);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     */
    private function paginateCollection(Collection $items, int $perPage): LengthAwarePaginator
    {
        $page = max(1, (int) request()->input('page', 1));
        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    public function transformRecords(AbstractPaginator $records): void
    {
        $records->setCollection(
            $records->getCollection()->map(function (array $row) {
                unset($row['sort_at']);

                return $row;
            })
        );
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
