<?php

namespace Modules\Space\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Space\Services\SpaceEventService;

class BookingController extends Controller
{
    public function __construct(
        private SpaceEventService $spaceEventService,
    ) {}

    public function index(Request $request)
    {
        $this->authorizeScopedAdmin();

        return Inertia::render('Space::BookingsIndex', [
            'title' => __('Booking'),
            'i18n' => [
                'performer' => __('Performer'),
                'pitch' => __('Pitch'),
                'start' => __('Start'),
                'end' => __('End'),
                'status' => __('Status'),
            ],
        ]);
    }

    public function records(Request $request)
    {
        $this->authorizeScopedAdmin();

        return $this->spaceEventService->getScopedAdminBookingRecords(
            $request->user(),
            $request->input('term'),
        );
    }

    private function authorizeScopedAdmin(): void
    {
        $user = auth()->user();

        if (! $user->isCityAdministrator() && ! $user->isSpecialEventsAdmin()) {
            abort(403);
        }
    }
}
