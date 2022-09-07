<?php

namespace Modules\Ecommerce\Services;

use App\Services\MediaService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Modules\Ecommerce\Entities\Order;
use Modules\Ecommerce\Entities\ScheduleBooking;
use Modules\Ecommerce\Enums\BookingStatus;
use Modules\Ecommerce\Enums\OrderStatus;

class OrderService
{
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function getRecords(
        string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $records = Order::orderBy('reference', 'DESC')
            ->when($term, function ($query) use ($term) {
                $query
                    ->where('reference', 'ILIKE', '%'.$term.'%')
                    ->orWhere('status', 'ILIKE', '%'.$term.'%')
                    ->orWhereHas('user', function (Builder $query) use ($term) {
                        $query->search($term);
                    });
            })
            ->paginate($perPage);

        $this->transformRecords($records);

        return $records;
    }

    public function transformRecords($records)
    {
        $records->getCollection()->transform(function ($record) {
            return (object) [
                'id' => $record->id,
                'reference' => $record->reference,
                'status' => Str::title($record->status),
                'customer_name' => $record->user->fullName ?? null,
                'date_placed' => $record->placed_at->toDateString(),
            ];
        });
    }

    public function getRecord(Order $order): array
    {
        $replicatedOrder = $order->replicate();
        $replicatedOrder->id = $order->id;

        $replicatedOrder->load('user', 'lines.purchasable.product');

        $orderSubset = $replicatedOrder
            ->append('user_full_name', 'formatted_placed_at')
            ->only('id', 'status', 'reference', 'formatted_placed_at', 'lines', 'user_full_name');

        $orderSubset['status'] = Str::title($orderSubset['status']);

        $orderSubset['lines'] = $orderSubset['lines']->map(function ($line) {
            $lineArray = $line->only('id', 'identifier', 'purchasable', 'latestEvent');

            $purchaseable = $lineArray['purchasable'];
            $lineArray['purchasable'] = [
                'name' => $purchaseable->product->translateAttribute('name'),
                'short_description' => $purchaseable->product->translateAttribute('short_description'),
                'sku' => $purchaseable->sku,
            ];

            $event = $lineArray['latestEvent'];
            $lineArray['event'] = [
                'booked_at' => $event->formattedBookedAt,
                'timezone' => $event->schedule->timezone,
                'duration' => $event->displayDuration,
                'status' => Str::title($event->status),
            ];

            unset($lineArray['latestEvent']);

            return $lineArray;
        });

        return $orderSubset;
    }

    public function cancelOrder(Order $order)
    {
        $order->status = OrderStatus::CANCELED->value;
        $order->save();
    }

    public function cancelEvent(ScheduleBooking $booking)
    {
        $booking->status = BookingStatus::CANCELED->value;
        $booking->save();
    }

    public function rescheduleEvent(ScheduleBooking $event, Carbon $dateTime)
    {
        $newEvent = $event->replicate();

        $newEvent->booked_at = $dateTime->format('Y-m-d H:i');
        $newEvent->status = BookingStatus::UPCOMING->value;
        $newEvent->save();

        $event->status = BookingStatus::RESCHEDULED->value;
        $event->save();

        return $newEvent;

    }
}
