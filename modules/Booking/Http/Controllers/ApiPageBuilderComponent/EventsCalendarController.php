<?php

namespace Modules\Booking\Http\Controllers\ApiPageBuilderComponent;

use App\Http\Controllers\Controller;
use Modules\Booking\Http\Requests\EventsCalendarRequest;
use Modules\Booking\ModuleService as BookingModuleService;
use Modules\Booking\Services\EventsCalendarService;

class EventsCalendarController extends Controller
{
    public function __construct(
        private EventsCalendarService $eventsCalendarService
    ) {}

    public function index(EventsCalendarRequest $request)
    {
        $scopes = [
            'dateRange' => $request->dates ?? []
        ];

        if (!empty($request->city)) {
            $scopes['city'] = $request->city;
        }

        if (!empty($request->country)) {
            $scopes['country'] = $request->country;
        }

        $pagination = $this
            ->eventsCalendarService
            ->getRecords(10, $scopes);

        $coordinates = $this->eventsCalendarService->getCoordinates($pagination);

        $center = $this->eventsCalendarService->getCenterCoordinate(
            $coordinates,
            BookingModuleService::centerCoordinate()
        );

        $distance = 0;

        if (count($coordinates) > 0) {
            if (!empty($center) && count($coordinates) > 1) {
                $farthest = $this->eventsCalendarService->getFarthestPoint(
                    $center,
                    $coordinates
                );

                $distance = $this->eventsCalendarService->getFarthestDistance(
                    $center,
                    $farthest
                );
            }
        }

        return response()->json([
            'pagination' => $pagination,
            'map' => [
                'center' => $center,
                'farthest_distance' => $distance,
                'zoom' => $this->eventsCalendarService->zoom($distance),
            ],
        ]);
    }

    public function getLocationOptions()
    {
        return $this->eventsCalendarService->getLocationOptions();
    }
}
