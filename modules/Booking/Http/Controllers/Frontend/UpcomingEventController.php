<?php

namespace Modules\Booking\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\Booking\Services\EventService;
use Modules\Booking\Http\Requests\UpcomingEventRequest;

class UpcomingEventController extends Controller
{
    public function events(
        UpcomingEventRequest $request,
        EventService $eventService,
    ) {
        $user = $request->getRouteUser();

        $scopes = [];

        if ($request->has('dates')) {
            $scopes['dateRange'] = $request->dates ?? [];
        }

        if ($request->has('city')) {
            $scopes['city'] = $request->city;
        }

        $minDate = Carbon::today()->subWeek();
        $maxDate = Carbon::today()->addYear();

        return [
            'records' => $eventService->getUpcomingEventsByUser($user->id, $scopes),
            'queryParams' => $request->only('dates', 'city'),
            'options' => $eventService->getUserUpcomingEventCityOptions($user->id, "- ".__('Select')." -"),
            'setting' => [
                'minDate' => $minDate->toDateString(),
                'maxDate' => $maxDate->toDateString(),
                'yearRange' => [$minDate->year, $maxDate->year],
            ],
        ];
    }
}
