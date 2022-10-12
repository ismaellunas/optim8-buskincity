<?php

namespace Modules\Booking\Http\Controllers;

use App\Http\Controllers\CrudController;
use Modules\Booking\Events\EventCanceled;
use Modules\Booking\Http\Requests\OrderCancelRequest;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Services\OrderService;

class OrderController extends CrudController
{
    private $orderService;

    public function __construct(
        OrderService $orderService,
    ) {
        $this->authorizeResource(Order::class, 'order');

        $this->orderService = $orderService;
    }

    public function cancel(OrderCancelRequest $request, Order $order)
    {
        $this->orderService->cancelOrder($order);

        $this->orderService->cancelEvent(
            $order->firstEventLine->latestEvent,
            $request->message
        );

        EventCanceled::dispatch($order);

        $this->generateFlashMessage('The Event has been canceled!');

        return back();
    }
}
