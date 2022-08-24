<?php

namespace Modules\Ecommerce\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use GetCandy\Models\Product;

class ProductService
{
    public function getRecords(
        string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $records = Product::orderBy('id', 'DESC')
            ->select(['id', 'status', 'attribute_data'])
            ->when($term, function ($query) use ($term) {
                $query->search($term);
            })
            ->paginate($perPage);

        $this->transformRecords($records);

        return $records;
    }

    public function transformRecords($records)
    {
        $records->getCollection()->transform(function ($record) {
            $record->id = $record->id;
            $record->name = $record->translateAttribute('name');
            $record->status = $record->status;

            return $record;
        });
    }

    public function roleOptions()
    {

    }
}
