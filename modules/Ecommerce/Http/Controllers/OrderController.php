<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\CrudController;
use Carbon\Carbon;
use Inertia\Inertia;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Events\EventRescheduled;
use Modules\Ecommerce\Events\OrderCanceled;
use Modules\Ecommerce\Http\Requests\OrderRescheduleRequest;
use Modules\Ecommerce\Services\EventService;
use Modules\Ecommerce\Services\OrderService;
use Modules\Ecommerce\Services\ProductService;

class OrderController extends CrudController
{
    protected $title = "Order";
    protected $baseRouteName = "admin.ecommerce.orders";

    private $eventService;
    private $orderService;

    public function __construct(
        EventService $eventService,
        OrderService $orderService
    ) {
        $this->authorizeResource(Order::class, 'order');

        $this->eventService = $eventService;
        $this->orderService = $orderService;
    }

    public function index()
    {
        $user = auth()->user();

        return Inertia::render('Ecommerce::OrderIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'pageQueryParams' => array_filter(request()->only('term')),
            'records' => $this->orderService->getRecords(request()->get('term')),
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
                'reschedule' => $user->can('reschedule', $order),
            ],
        ]));
    }

    public function cancel(Order $order)
    {
        $this->authorize('cancel', $order);

        $this->orderService->cancelOrder($order);

        $this->orderService->cancelEvent($order->firstEventLine->latestEvent);

        OrderCanceled::dispatch($order);

        $this->generateFlashMessage('The Event has been canceled!');

        return back();
    }

    public function reschedule(Order $order)
    {
        $this->authorize('reschedule', $order);

        $eventLine = $order->firstEventLine;
        $product = $eventLine->purchasable->product;
        $minDate = today();
        $maxDate = today()->addDays($product->bookable_date_range);

        $schedule = $product->eventSchedule;

        $disabledDates = $this->eventService->disabledDates($schedule, $minDate, $maxDate);

        return Inertia::render('Ecommerce::OrderReschedule', $this->getData([
            'title' => 'Reschedule Event',
            'order' => $this->orderService->getRecord($order),
            'product' => app(ProductService::class)->formResource($product),
            'minDate' => $minDate->toDateString(),
            'maxDate' => $maxDate->toDateString(),
            'disabledDates' => $disabledDates,
            'timezone' => $eventLine->latestEvent->schedule->timezone,
        ]));
    }

    public function availableTimes(Order $order, string $date)
    {
        $this->authorize('reschedule', $order);

        $eventLine = $order->firstEventLine;
        $product = $eventLine->purchasable->product;
        $schedule = $product->eventSchedule;

        return $this->eventService->availableTimes($schedule, Carbon::parse($date));
    }

    public function rescheduleUpdate(OrderRescheduleRequest $request, Order $order)
    {
        $inputs = $request->all();

        $this->orderService->rescheduleEvent(
            $order->firstEventLine->latestEvent,
            Carbon::parse($inputs['date']. ' '.$inputs['time'])
        );

        EventRescheduled::dispatch($order);

        $this->generateFlashMessage('The Event has been rescheduled!');

        return redirect()->route($this->baseRouteName.'.show', [$order->id]);
    }
}
