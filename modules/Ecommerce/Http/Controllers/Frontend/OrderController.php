<?php

namespace Modules\Ecommerce\Http\Controllers\Frontend;

use App\Http\Controllers\CrudController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Events\EventBooked;
use Modules\Ecommerce\Http\Requests\EventBookRequest;
use Modules\Ecommerce\Http\Requests\OrderRescheduleRequest;
use Modules\Ecommerce\Services\EventService;
use Modules\Ecommerce\Services\OrderService;

class OrderController extends CrudController
{
    protected $title = "Order";
    protected $baseRouteName = "ecommerce.orders";

    private $eventService;
    private $orderService;

    public function __construct(
        EventService $eventService,
        OrderService $orderService
    ) {
        $this->eventService = $eventService;
        $this->orderService = $orderService;
    }

    public function index()
    {
        $user = auth()->user();

        return Inertia::render('Ecommerce::FrontendOrderIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'orders' => $this->orderService->getFrontendRecords(
                $user,
                request()->get('term')
            ),
            'pageQueryParams' => array_filter(request()->only('term')),
        ]));
    }

    public function show(Order $order)
    {
        $user = auth()->user();

        return Inertia::render('Ecommerce::FrontendOrderShow', $this->getData([
            'title' => $this->title.' #'.$order->reference,
        ]));
    }

    public function cancel(Order $order)
    {
    }

    public function reschedule(Order $order)
    {
    }

    public function availableTimes(Order $order, string $date)
    {
    }

    public function rescheduleUpdate(OrderRescheduleRequest $request, Order $order)
    {
    }

    public function bookEvent(EventBookRequest $request, Product $product)
    {
        $inputs = $request->all();

        $order = $this->orderService->bookEvent(
            $product,
            Carbon::parse($inputs['date']. ' '.$inputs['time']),
            auth()->user(),
        );

        EventBooked::dispatch($order);

        $this->generateFlashMessage('The Event has been booked!');

        return redirect()->route($this->baseRouteName.'.index');
    }
}
