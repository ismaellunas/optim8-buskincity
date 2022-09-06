<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Events\OrderCanceled;
use Modules\Ecommerce\Services\OrderService;

class OrderController extends CrudController
{
    protected $title = "Order";
    protected $baseRouteName = "admin.ecommerce.orders";

    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;

        $this->authorizeResource(Order::class, 'order');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        return Inertia::render('Ecommerce::OrderIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'pageQueryParams' => array_filter($request->only('term')),
            'records' => $this->orderService->getRecords(
                $request->term
            ),
            'can' => [
                'read' => $user->can('order.read'),
            ],
        ]));
    }

    public function show(Order $order)
    {
        $user = auth()->user();

        return Inertia::render('Ecommerce::OrderShow', $this->getData([
            'title' => $this->title.' #'.$order->reference,
            'order' => $this->orderService->getRecord($order),
            'can' => [
                'cancel' => $user->can('cancel', $order),
            ],
        ]));
    }

    public function edit($id)
    {
    }

    public function update(Request $request, $id)
    {
    }

    public function cancel(Request $request, Order $order)
    {
        $this->authorize('cancel', $order);

        $this->orderService->cancelOrder($order);
        $this->orderService->cancelEvent($order->lines->first()->scheduleBooking);

        OrderCanceled::dispatch($order);

        return back();
    }
}
