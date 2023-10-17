<?php

namespace Modules\Booking\Services;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Modules\Ecommerce\Entities\Product;
use Modules\Space\Entities\Space;

class ProductSpaceService
{
    public function getSpaceOptions(int $exceptId = null): array
    {
        return Space::select(['id', 'name'])
            ->whereDoesntHave('product', function (Builder $query) use ($exceptId) {
                $query->whereNotIn('productable_id', [$exceptId]);
            })
            ->orderBy('name')
            ->get()
            ->asOptions('id', 'name')
            ->all();
    }

    public function formResource(Product $product): array
    {
        $space = $product->productable;

        return [
            'id' => $space->id ?? null,
        ];
    }
}