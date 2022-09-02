<?php

namespace Modules\Ecommerce\Services;

use App\Services\MediaService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Modules\Ecommerce\Entities\Order;

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
            //->select(['id', 'status', 'attribute_data'])
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

    public function getRecord($order): array
    {
        $orderSubset = $order
            ->append('user_full_name', 'formatted_placed_at')
            ->only('id', 'status', 'reference', 'formatted_placed_at', 'lines', 'user_full_name');

        $orderSubset['status'] = Str::title($orderSubset['status']);

        $orderSubset['lines']->transform(function ($line) {
            $lineArray = $line->only('id', 'identifier', 'purchasable', 'scheduleBooking');

            $purchaseable = $lineArray['purchasable'];
            $lineArray['purchasable'] = [
                'name' => $purchaseable->product->translateAttribute('name'),
                'short_description' => $purchaseable->product->translateAttribute('short_description'),
                'sku' => $purchaseable->sku,
            ];

            $scheduleBooking = $lineArray['scheduleBooking'];
            $lineArray['scheduleBooking'] = [
                'booked_at' => $scheduleBooking->formattedBookedAt,
                'timezone' => $scheduleBooking->schedule->timezone,
                'duration' => $scheduleBooking->displayDuration,
                'status' => Str::title($scheduleBooking->status),
            ];

            return $lineArray;
        });

        return $orderSubset;
    }
}
