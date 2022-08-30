<?php

namespace Modules\Ecommerce\Services;

use App\Services\MediaService;
use GetCandy\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

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
}
