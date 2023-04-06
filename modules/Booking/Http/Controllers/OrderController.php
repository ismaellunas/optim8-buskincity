<?php

namespace Modules\Booking\Http\Controllers;

use App\Http\Controllers\CrudController;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Modules\Booking\Events\EventCanceled;
use Modules\Booking\Events\EventRescheduled;
use Modules\Booking\Http\Requests\OrderCancelRequest;
use Modules\Booking\Http\Requests\OrderIndexRequest;
use Modules\Booking\Http\Requests\OrderRescheduleRequest;
use Modules\Booking\Services\EventService;
use Modules\Booking\Services\ProductEventService;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Services\OrderService;
use Modules\Ecommerce\Services\ProductService;

class OrderController extends CrudController
{
    private $orderService;
    private $productEventService;
    private $eventService;

    protected $title = "Booking";
    protected $baseRouteName = "admin.booking.orders";

    public function __construct(
        EventService $eventService,
        OrderService $orderService,
        ProductEventService $productEventService
    ) {
        $this->authorizeResource(Order::class, 'order');

        $this->eventService = $eventService;
        $this->orderService = $orderService;
        $this->productEventService = $productEventService;
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

    public function index(OrderIndexRequest $request)
    {
        $user = auth()->user();

        $scopes = collect([
            'city' => $request->city ?? null,
            'country' => $request->country ?? null,
            'inStatus' => $request->status ? [$request->status] : null,
        ]);

        if (is_array($request->dates) && !empty(array_filter($request->dates))) {
            $scopes->put('dateRange', $request->dates);
        }

        return Inertia::render('Booking::OrderIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'pageQueryParams' => array_filter($request->only(
                'city',
                'country',
                'dates',
                'status',
                'term',
            )),
            'records' => $this->orderService->getRecords(
                $user,
                $request->get('term'),
                $scopes->all(),
            ),
            'locationOptions' => $this->orderService->getLocationOptions(
                auth()->user(),
                $scopes->except('city', 'country')->all(),
            ),
            'statusOptions' => $this->orderService->statusOptions(
                $user,
                $scopes->except('inStatus')->all(),
                __('Status')
            ),
            'can' => [
                'read' => (
                    $user->can('order.read')
                    || $user->isProductManager()
                ),
            ],
        ]));
    }

    public function show(Order $order)
    {
        $user = auth()->user();

        $checkIn = $order->checkIn;
        $orderRecord = $this->orderService->getRecord($order);

        $productName = Arr::get($orderRecord, 'product.name');

        return Inertia::render('Booking::OrderShow', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $productName,
                ],
            ],
            'title' => $this->title.': '.Arr::get($orderRecord, 'product.name'),
            'order' => $orderRecord,
            'checkInTime' => $checkIn
                ? $checkIn
                    ->checked_in_at
                    ->setTimezone($orderRecord['event']['timezone'])
                    ->format(config('constants.format.time_checkin'))
                : null,
            'can' => [
                'cancel' => $user->can('cancelBooking', $order),
                'reschedule' => $user->can('rescheduleBooking', $order),
            ],
            'googleApiKey' => app(SettingService::class)->getGoogleApi(),
        ]));
    }

    public function reschedule(Order $order)
    {
        $eventLine = $order->firstEventLine;
        $product = $eventLine->purchasable->product;

        $minDate = $this->productEventService->minBookableDate();
        $maxDate = $this->productEventService->maxBookableDate($product);

        $product = app(ProductService::class)->formResource($product);

        return Inertia::render('Booking::OrderReschedule', $this->getData([
            'breadcrumbs' => [
                [
                    'title' => $this->getIndexTitle(),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                [
                    'title' => $product['name'],
                    'url' => route($this->baseRouteName.'.show', $order->id),
                ],
                [
                    'title' => __('Reschedule Event'),
                ],
            ],
            'order' => $this->orderService->getRecord($order),
            'product' => $product,
            'minDate' => $minDate->toDateString(),
            'maxDate' => $maxDate->toDateString(),
            'timezone' => $eventLine->latestEvent->schedule->timezone,
            'allowedDatesRouteName' => 'admin.booking.products.allowed-dates',
            'availableTimesRouteName' => $this->productEventService->availableTimesOrderRouteName(),
        ]));
    }

    public function rescheduleUpdate(OrderRescheduleRequest $request, Order $order)
    {
        $inputs = $request->validated();

        $this->orderService->rescheduleEvent(
            $order->firstEventLine->latestEvent,
            Carbon::parse($inputs['date']. ' '.$inputs['time']),
            $inputs['message']
        );

        EventRescheduled::dispatch($order);

        $this->generateFlashMessage('The Event has been rescheduled!');

        return redirect()->route($this->baseRouteName.'.show', [$order->id]);
    }

    public function availableTimes(Order $order, string $date)
    {
        $eventLine = $order->firstEventLine;
        $product = $eventLine->purchasable->product;
        $schedule = $product->eventSchedule;

        return $this->eventService->availableTimes($schedule, $date);
    }
}
