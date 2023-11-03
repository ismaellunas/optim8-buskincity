<?php

namespace Modules\Booking\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Ecommerce\Entities\Product;
use Modules\Space\Entities\Space;

class ProductSpaceService
{
    public function getSpaceOptions(int $exceptId = null): Collection
    {
        $user = auth()->user();
        $spaces = null;

        if ($user->isSpaceManagerOnlyAccess()) {
            $spaces = $user
                ->spaces
                ->map(fn ($space) => $space->only('id', '_lft', '_rgt'));
        }

        $columnNames = ['id', 'name', 'parent_id', 'type_id', '_lft', '_rgt'];

        $isExceptIdEnabled = (
            $spaces
            && $exceptId
        );

        return Space::select($columnNames)
            ->with('product')
            ->when($spaces, function (Builder $query, $spaces) {
                foreach ($spaces as $key => $space) {
                    $boolean = $key == 0 ? 'and' : 'or';
                    $query->whereDescendantOrSelf($space, $boolean);
                }
            })
            ->when($isExceptIdEnabled, function (Builder $query) use ($exceptId) {
                $query->orWhereHas('product', function ($query) use ($exceptId) {
                    $query->where('productable_id', $exceptId);
                });
            })
            ->withDepth()
            ->defaultOrder()
            ->get()
            ->map(function ($space) use ($exceptId) {
                $note = null;
                $hasSpaceProduct = (
                    !! $space->product
                    && $space->product->productable_id != $exceptId
                );

                if ($hasSpaceProduct) {
                    $note = __('Already in use in :product', [
                        'product' => $space->product->displayName,
                    ]);
                }

                return [
                    'id' => $space->id,
                    'value' => $space->name,
                    'depth' => $space->depth,
                    'is_disabled' => $hasSpaceProduct,
                    'note' => $note,
                ];
            });
    }

    public function formResource(Product $product): array
    {
        $space = $product->productable;

        return [
            'id' => $space->id ?? null,
        ];
    }

    public function unAssignSpaceFromProducts(): void
    {
        $products = Product::where('productable_type', Space::class)->get();

        foreach ($products as $product) {
            $product->productable_type = null;
            $product->productable_id = null;

            $product->save();
        }
    }
}
