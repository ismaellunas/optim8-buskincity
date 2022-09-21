<?php

namespace Modules\Ecommerce\Http\Controllers\Frontend;

use App\Http\Controllers\CrudController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Enums\BookingStatus;
use Modules\Ecommerce\Events\EventBooked;
use Modules\Ecommerce\Events\EventCanceled;
use Modules\Ecommerce\Events\EventRescheduled;
use Modules\Ecommerce\Http\Requests\EventBookRequest;
use Modules\Ecommerce\Http\Requests\OrderRescheduleRequest;
use Modules\Ecommerce\Services\EventService;
use Modules\Ecommerce\Services\OrderService;
use Modules\Ecommerce\Services\ProductEventService;

class OrderController extends CrudController
{
    protected $title = "Order";
    protected $baseRouteName = "ecommerce.orders";

    private $eventService;
    private $orderService;
    private $productEventService;

    public function __construct(
        EventService $eventService,
        OrderService $orderService,
        ProductEventService $productEventService
    ) {
        $this->eventService = $eventService;
        $this->orderService = $orderService;
        $this->productEventService = $productEventService;
    }

    private function availableTimesRouteName(): string
    {
        return "ecommerce.products.available-times";
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

    public function cancel(Order $order)
    {
        $this->authorize('cancel', $order);

        $this->orderService->cancelOrder($order);

        $this->orderService->cancelEvent($order->firstEventLine->latestEvent);

        EventCanceled::dispatch($order);

        $this->generateFlashMessage('The Event has been canceled!');

        return back();
    }

    public function reschedule(Order $order)
    {
        $this->authorize('cancel', $order);

        $eventLine = $order->firstEventLine;
        $product = $eventLine->purchasable->product;
        $schedule = $product->eventSchedule;

        $minDate = $this->productEventService->minBookableDate();
        $maxDate = $this->productEventService->maxBookableDate($product);

        $disabledDates = $this->eventService->disabledDates($schedule, $minDate, $maxDate);

        return Inertia::render('Ecommerce::FrontendOrderReschedule', $this->getData([
            'title' => 'Reschedule Event',
            'order' => $this->orderService->getFrontendRecord($order),
            'minDate' => $minDate->toDateString(),
            'maxDate' => $maxDate->toDateString(),
            'disabledDates' => $disabledDates,
            'timezone' => $eventLine->latestEvent->schedule->timezone,
            'availableTimesRouteName' => $this->availableTimesRouteName(),
        ]));
    }

    public function rescheduleUpdate(OrderRescheduleRequest $request, Order $order)
    {
        $this->orderService->rescheduleEvent(
            $order->firstEventLine->latestEvent,
            Carbon::parse($request->get('date'). ' '.$request->get('time'))
        );

        EventRescheduled::dispatch($order);

        $this->generateFlashMessage('The Event has been rescheduled!');

        return redirect()->route($this->baseRouteName.'.show', [$order->id]);
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
