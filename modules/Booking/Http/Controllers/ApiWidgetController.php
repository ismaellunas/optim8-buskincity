<?php

namespace Modules\Booking\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Ecommerce\Services\OrderService;

class ApiWidgetController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function getLatestBookings(Request $request)
    {
        $scopes = [
            'inStatus' => $request->status ?? null,
            'city' => $request->city ?? null,
        ];

        return [
            'records' => $this->orderService->getWidgetRecords(
                auth()->user(),
                $request->term,
                $scopes,
            ),
        ];
    }
}
