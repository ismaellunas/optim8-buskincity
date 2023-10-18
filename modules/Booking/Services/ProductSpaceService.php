<?php

namespace Modules\Booking\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Ecommerce\Entities\Product;
use Modules\Space\Entities\Space;

class ProductSpaceService
{
    public function getSpaceOptions(int $exceptId = null): array
    {
        $user = auth()->user();
        $spaceIds = $user->spaces->pluck('id')->all();

        return Space::select(['id', 'name'])
            ->when($spaceIds, function (Builder $query, $spaceIds) {
                $query->whereIn('id', $spaceIds);
            })
            ->whereDoesntHave('product', function (Builder $query) use ($exceptId) {
                $query->where('productable_id', '!=',  $exceptId);
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