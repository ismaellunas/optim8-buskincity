<?php

namespace Modules\Space\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceEventService;

class SpaceEventController extends Controller
{
    public function events(Request $request, Space $space, SpaceEventService $spaceEventService)
    {
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
            'queryParams' => $request->only('dates'),
            'options' => $spaceEventService->getSpaceRecordOptions($space, $scopes, "-".__('Space')."-"),
            'minDate' => $minDate->toDateString(),
            'maxDate' => $maxDate->toDateString(),
            'yearRange' => [$minDate->year, $maxDate->year],
            'isLeaf' => $space->isLeaf(),
        ];
    }
}
