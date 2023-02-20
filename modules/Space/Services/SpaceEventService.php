<?php

namespace Modules\Space\Services;

use App\Helpers\GoogleMap;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
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
        $spaceEvents = SpaceEvent::where(function ($query) use ($space, $scopes) {
                $this->scopeRecords($query, $space, $scopes);
            })
            ->with([
                'space' => function ($query) {
                    $query->select([
                        'id', 'page_id', '_lft', '_rgt', 'parent_id', 'is_page_enabled', 'type_id', 'address', 'latitude', 'longitude', 'updated_at'
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

            $shortDescription = nl2br(preg_replace("/[\r\n]+/", "\n", Purifier::clean(
                !empty($event->excerpt)
                ? $event->excerpt
                : Str::words($event->description, 60, ' ...')
            )));

            $data = [
                'id' => $event->id,
                'started_at' => $event->started_at->format('d M Y H:i'),
                'ended_at' => $event->ended_at->format('d M Y H:i'),
                'title' => $event->title,
                'short_description' => $shortDescription,
                'description' => nl2br(Purifier::clean($event->description)),
                'space_name' => $space->name,
                'space_url' => $space->pageLocalizeURL(currentLocale()),
                'address' => $space->address,
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

    public function getSpaceRecordOptions(
        Space $space,
        string $noneLabel = null
    ): Collection {
        if (is_null($this->cacheSpaceOptions)) {
            $this->cacheSpaceOptions = SpaceEvent::
                where(function ($query) use ($space) {
                    $this->scopeRecords($query, $space);
                })
                ->with([
                    'space' => function ($query) {
                        $query->select(['id', 'name']);
                        $query->withDepth();
                    },
                ])
                ->get(['id', 'space_id']);
        }

        $options = $this->cacheSpaceOptions
            ->pluck('space')
            ->unique()
            ->sortBy([
                ['depth', 'asc'],
                ['name', 'asc'],
            ])
            ->map(fn ($space) => [
                'id' => $space->id,
                'value' => $space->name,
                'depth' => $space->depth,
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
