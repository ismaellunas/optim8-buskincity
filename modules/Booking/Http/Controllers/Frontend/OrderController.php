<?php

namespace Modules\Booking\Http\Controllers\Frontend;

use App\Http\Controllers\CrudController;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Modules\Booking\Enums\BookingStatus;
use Modules\Booking\Events\EventBooked;
use Modules\Booking\Events\EventRescheduled;
use Modules\Booking\Http\Requests\EventBookRequest;
use Modules\Booking\Http\Requests\OrderRescheduleRequest;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\Product;
use Modules\Ecommerce\Services\OrderService;

class OrderController extends CrudController
{
    private $orderService;
    private $productEventService;

    protected $title = "Booking";
    protected $baseRouteName = "booking.orders";

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

        return Inertia::render('Booking::FrontendOrderIndex', $this->getData([
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
        $order->load('firstEventline.latestEvent');

        $product = $order->firstProduct;
        $event = $order->firstEventLine->latestEvent;
        $checkIn = $order->checkIn;
        $user = auth()->user();

        return Inertia::render('Booking::FrontendOrderShow', $this->getData([
            'title' => $product->displayName,
            'description' => $event->timezonedBookedAt->format(config('ecommerce.format.date_event_email_title')),
            'order' => $this->orderService->getFrontendRecord($order),
            'checkInTime' => $checkIn
                ? $checkIn
                    ->checked_in_at
                    ->setTimezone($event->schedule->timezone)
                    ->format('H:i (\G\M\T P)')
                : null,
            'can' => [
                'cancel' => $user->can('cancel', $order),
                'reschedule' => $user->can('reschedule', $order),
                'checkIn' => $user->can('checkIn', $order),
            ],
            'breadcrumbs' => [
                [
                    'title' => Str::plural($this->title),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                ['title' => $product->displayName],
            ],
        ]));
    }

    public function reschedule(Order $order)
    {
        $eventLine = $order->firstEventLine;
        $product = $eventLine->purchasable->product;

        $minDate = $this->productEventService->minBookableDate();
        $maxDate = $this->productEventService->maxBookableDate($product);

        return Inertia::render('Booking::FrontendOrderReschedule', $this->getData([
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
