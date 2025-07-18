<?php

namespace Modules\Booking\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Booking\Http\Requests\LatestBookingWidgetRequest;
use Modules\Ecommerce\Services\OrderService;

class ApiWidgetController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function getLatestBookings(LatestBookingWidgetRequest $request)
    {
        $scopes = collect([
            'inStatus' => $request->status ? [$request->status] : null,
            'city' => $request->city ?? null,
            'country' => $request->country ?? null,
        ]);

        if (is_array($request->dates) && !empty(array_filter($request->dates))) {
            $scopes->put('dateRange', $request->dates);
        }

        return [
            'records' => $this->orderService->getWidgetRecords(
                auth()->user(),
                $request->term,
                $scopes->all(),
            ),
            'options' => [
                'status' => $this->orderService->statusOptions(
                    auth()->user(),
                    $scopes->except('inStatus')->all(),
                    __('Status')
                ),
                'location' => $this->orderService->getLocationOptions(
                    auth()->user(),
                    $scopes->except('city', 'country')->all(),
                ),
            ],
        ];
    }
}
