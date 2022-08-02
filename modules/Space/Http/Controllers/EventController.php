<?php

namespace Modules\Space\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Event\Entities\Event;
use Modules\Space\Entities\Space;
use Modules\Space\Http\Requests\EventRequest;
use Modules\Space\Services\EventService;

class EventController extends Controller
{
    private $title = "Event";

    private $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function store(EventRequest $request, Space $space)
    {
        $inputs = $request->all();

        $event = $this->eventService->createEvent($space, $inputs);

        return [
            'event' => $this->eventService->getEditableRecord($space, $event),
            'message' => $this->title.' created successfully!',
        ];
    }

    public function update(EventRequest $request, Space $space, Event $event)
    {
        $inputs = $request->all();

        $this->eventService->updateEvent($event, $inputs);

        return [
            'event' => $this->eventService->getEditableRecord($space, $event),
            'message' => $this->title.' updated successfully!',
        ];
    }

    public function show(Space $space, Event $event)
    {
        return $this->eventService->getEditableRecord($space, $event);
    }

    public function destroy(Space $space, Event $event)
    {
        $event->delete();

        return response($this->title.' deleted successfully!', 200);
    }

    public function records(Request $request, Space $space)
    {
        $records = $this->eventService->getRecords($space, $request->term);

        $this->eventService->transformRecords($records);

        return $records;
    }
}
