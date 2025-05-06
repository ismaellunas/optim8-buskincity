<?php

namespace Modules\Booking\Http\Controllers\Frontend;

use App\Http\Controllers\CrudController;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Modules\Booking\Events\EventBooked;
use Modules\Booking\Events\EventRescheduled;
use Modules\Booking\Http\Requests\EventBookRequest;
use Modules\Booking\Http\Requests\OrderIndexRequest;
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
        $this->authorizeResource(Order::class, 'order');

        $this->orderService = $orderService;
        $this->productEventService = $productEventService;
    }

    public function index(OrderIndexRequest $request)
    {
        $user = auth()->user();

        $scopes = collect([
            'inStatus' => $request->status ? [$request->status] : null,
            'orderByColumn' => [
                'column' => $request->column,
                'order' => $request->order,
            ],
            'city' => $request->city ?? null,
            'country' => $request->country ?? null,
        ]);

        if (is_array($request->dates) && !empty(array_filter($request->dates))) {
            $scopes->put('dateRange', $request->dates);
        }

        return Inertia::render('Booking::FrontendOrderIndex', $this->getData([
            'title' => $this->getIndexTitle(),
            'orders' => $this->orderService->getFrontendRecords(
                $user,
                $request->get('term'),
                $scopes->all(),
            ),
            'pageQueryParams' => array_filter(
                $request->only(
                    'city',
                    'country',
                    'column',
                    'dates',
                    'order',
                    'status',
                    'term'
                )
            ),
            'locationOptions' => $this->orderService->getLocationOptions(
                auth()->user(),
                $scopes->except('city', 'country')->all(),
                true
            ),
            'statusOptions' => $this->orderService->statusOptions(
                $user,
                $scopes->only('city')->all(),
                __('Status'),
                true
            ),
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
                    ->format(config('constants.format.time_checkin'))
                : null,
            'can' => [
                'cancel' => $user->can('cancelBooking', $order),
                'reschedule' => $user->can('rescheduleBooking', $order),
                'checkIn' => $user->can('checkIn', $order),
            ],
            'breadcrumbs' => [
                [
                    'title' => Str::plural($this->title),
                    'url' => route($this->baseRouteName.'.index'),
                ],
                ['title' => $product->displayName],
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
