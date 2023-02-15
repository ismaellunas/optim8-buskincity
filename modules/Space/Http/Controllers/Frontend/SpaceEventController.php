<?php

namespace Modules\Space\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\Space\Services\SpaceEventService;
use Modules\Space\Http\Requests\Frontend\SpaceEventRequest;

class SpaceEventController extends Controller
{
    public function events(
        SpaceEventRequest $request,
        SpaceEventService $spaceEventService,
    ) {
        $space = $request->getSpace();

        $scopes = [];

        if ($request->has('dates')) {
            $scopes['dateRange'] = $request->dates ?? [];
        }

        if ($request->has('space')) {
            $scopes['hasSpace'] = $request->space;
        }

        $minDate = Carbon::today()->subWeek();
        $maxDate = Carbon::today()->addYear();

        return [
            'records' => $spaceEventService->getSpaceEventRecords($space, $scopes),
            'queryParams' => $request->only('dates', 'space'),
            'options' => [
                'spaces' => $spaceEventService->getSpaceRecordOptions($space, "- ".__('Select')." -"),
            ],
            'setting' => [
                'minDate' => $minDate->toDateString(),
                'maxDate' => $maxDate->toDateString(),
                'yearRange' => [$minDate->year, $maxDate->year],
                'isLeaf' => $space->isLeaf(),
            ],
        ];
    }
}
