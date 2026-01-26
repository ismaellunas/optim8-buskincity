<?php

namespace Modules\Space\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Space\Entities\Space;
use Modules\Space\Entities\SpaceEvent;
use Modules\Space\Http\Requests\SpaceEventRequest;
use Modules\Space\Services\EventService;

class EventController extends Controller
{
    private $title = 'space_module::terms.space event';

    private $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function store(SpaceEventRequest $request, Space $space)
    {
        $inputs = $request->validated();

        $event = $this->eventService->createEvent($space, $inputs);

        return [
            'event' => $this->eventService->getEditableRecord($event),
            'message' => __('The :resource was created!', ['resource' => __($this->title)]),
        ];
    }

    public function update(SpaceEventRequest $request, Space $space, SpaceEvent $event)
    {
        $inputs = $request->validated();

        $this->eventService->updateEvent($event, $inputs);

        return [
            'event' => $this->eventService->getEditableRecord($event),
            'message' => __('The :resource was updated!', ['resource' => __($this->title)]),
        ];
    }

    public function show(Space $space, SpaceEvent $event)
    {
        return $this->eventService->getEditableRecord($event);
    }

    public function destroy(Space $space, SpaceEvent $event)
    {
        $event->delete();

        return response(__('The :resource was deleted!', ['resource' => __($this->title)]), 200);
    }

    public function records(Request $request, Space $space)
    {
        $records = $this->eventService->getRecords($space, $request->term);

        $this->eventService->transformRecords($records);

        return $records;
    }
}
