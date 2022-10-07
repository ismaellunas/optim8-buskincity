<?php

namespace Modules\Ecommerce\Http\Controllers\Frontend;

use App\Http\Controllers\CrudController;
use Carbon\Carbon;
use Inertia\Inertia;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Enums\BookingStatus;
use Modules\Ecommerce\Events\EventBooked;
use Modules\Ecommerce\Events\EventCanceled;
use Modules\Ecommerce\Events\EventRescheduled;
use Modules\Ecommerce\Http\Requests\EventBookRequest;
use Modules\Ecommerce\Http\Requests\OrderCancelRequest;
use Modules\Ecommerce\Http\Requests\OrderRescheduleRequest;
use Modules\Ecommerce\Services\OrderService;
use Modules\Ecommerce\Services\ProductEventService;

class OrderController extends CrudController
{
    protected $title = "Order";
    protected $baseRouteName = "ecommerce.orders";

    private $orderService;
    private $productEventService;

    public function __construct(
        OrderService $orderService,
        ProductEventService $productEventService
    ) {
        $this->orderService = $orderService;
        $this->productEventService = $productEventService;
    }

    public function index()
    {
        $user = auth()->user();

        return Inertia::render('Ecommerce::FrontendOrderIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'orders' => $this->orderService->getFrontendRecords(
                $user,
                request()->get('term'),
                ['inStatus' => request()->status ?? null],
            ),
            'pageQueryParams' => array_filter(request()->only('term', 'status')),
            'statusOptions' => BookingStatus::options(),
        ]));
    }

    public function show(Order $order)
    {
        $product = $order->firstEventLine->purchasable->product;
        $event = $order->firstEventLine->latestEvent;
        $user = auth()->user();

        return Inertia::render('Ecommerce::FrontendOrderShow', $this->getData([
            'title' => $product->displayName,
            'description' => $event->timezonedBookedAt->format(config('ecommerce.format.date_event_email_title')),
            'order' => $this->orderService->getFrontendRecord($order),
            'can' => [
                'cancel' => $user->can('cancel', $order),
                'reschedule' => $user->can('reschedule', $order),
            ],
        ]));
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

    public function reschedule(Order $order)
    {
        $eventLine = $order->firstEventLine;
        $product = $eventLine->purchasable->product;

        $minDate = $this->productEventService->minBookableDate();
        $maxDate = $this->productEventService->maxBookableDate($product);

        return Inertia::render('Ecommerce::FrontendOrderReschedule', $this->getData([
            'title' => 'Reschedule Event',
            'order' => $this->orderService->getFrontendRecord($order),
            'minDate' => $minDate->toDateString(),
            'maxDate' => $maxDate->toDateString(),
            'timezone' => $eventLine->latestEvent->schedule->timezone,
            'allowedDatesRouteName' => $this->productEventService->allowedDatesRouteName(),
            'availableTimesRouteName' => $this->productEventService->availableTimesRouteName(),
        ]));
    }

    public function rescheduleUpdate(OrderRescheduleRequest $request, Order $order)
    {
        $this->orderService->rescheduleEvent(
            $order->firstEventLine->latestEvent,
            Carbon::parse($request->get('date'). ' '.$request->get('time')),
            $request->message
        );

        EventRescheduled::dispatch($order);

        $this->generateFlashMessage('The Event has been rescheduled!');

        return redirect()->route($this->baseRouteName.'.show', [$order->id]);
    }

    public function bookEvent(EventBookRequest $request, Product $product)
    {
        $inputs = $request->validated();

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
