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
        $scopes = [
            'inStatus' => $request->status ?? null,
            'city' => $request->city ?? null,
        ];

        if (is_array($request->dates) && !empty(array_filter($request->dates))) {
            $scopes['dateRange'] = $request->dates;
        }

        return [
            'records' => $this->orderService->getWidgetRecords(
                auth()->user(),
                $request->term,
                $scopes,
            ),
        ];
    }
}
