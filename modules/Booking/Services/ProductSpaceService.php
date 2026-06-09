<?php

namespace Modules\Booking\Services;

use App\Services\UserScopeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Ecommerce\Entities\Product;
use Modules\Space\Entities\Space;
use Modules\Space\Services\SpaceService;

class ProductSpaceService
{
    public function getSpaceOptions(int $exceptId = null): Collection
    {
        $user = auth()->user();
        $scopeService = app(UserScopeService::class);
        $spaces = null;
        $excludeTypeIds = $this->nonAssignableTypeIds();

        if ($scopeService->requiresSavedLocationForPitch($user)) {
            $cityIds = $user->isSpecialEventsAdmin()
                ? $user->scopeIdsFor(config('permission.role_names.special_events_admin'), 'city')
                : $user->adminCities->pluck('id')->all();

            $managedSpaceIds = $user->spaces->pluck('id')->all();
            $citySpaceIds = $cityIds === []
                ? []
                : Space::whereIn('city_id', $cityIds)->pluck('id')->all();
            $spaceIds = array_values(array_unique(array_merge($managedSpaceIds, $citySpaceIds)));

            if ($spaceIds === []) {
                return collect();
            }

            $spaces = Space::whereIn('id', $spaceIds)
                ->get()
                ->map(fn ($space) => $space->only('id', '_lft', '_rgt'));
        } elseif ($user->isSpaceManagerOnlyAccess()) {
            $spaces = $user
                ->spaces
                ->map(fn ($space) => $space->only('id', '_lft', '_rgt'));
        }

        $columnNames = [
            'id', 'name', 'parent_id', 'type_id', '_lft', '_rgt',
            'address', 'city', 'city_id', 'country_code', 'latitude', 'longitude',
        ];

        $isExceptIdEnabled = (
            $spaces instanceof Collection
            && $spaces->isNotEmpty()
            && $exceptId
        );

        $requiresSavedLocation = $scopeService->requiresSavedLocationForPitch($user);

        return Space::select($columnNames)
            ->with('product')
            ->when($spaces instanceof Collection && $spaces->isNotEmpty(), function (Builder $query, $spaces) {
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
            ->map(function ($space) use ($exceptId, $excludeTypeIds, $requiresSavedLocation) {
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

                $isStructuralNode = in_array((int) $space->type_id, $excludeTypeIds, true);

                return [
                    'id' => $space->id,
                    'value' => $space->name,
                    'depth' => $space->depth,
                    'is_disabled' => $hasSpaceProduct || ($requiresSavedLocation && $isStructuralNode),
                    'note' => $note,
                    'location' => [
                        'address' => $space->address,
                        'city' => $space->cityName(),
                        'city_id' => $space->city_id,
                        'country_code' => $space->country_code,
                        'latitude' => $space->latitude,
                        'longitude' => $space->longitude,
                    ],
                ];
            });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function locationPayloadFromSpace(?int $spaceId): ?array
    {
        if (! $spaceId) {
            return null;
        }

        $space = Space::query()->find($spaceId);

        if (! $space) {
            return null;
        }

        return [
            'address' => $space->address,
            'city' => $space->cityName(),
            'country_code' => $space->country_code,
            'latitude' => $space->latitude,
            'longitude' => $space->longitude,
            'city_id' => $space->city_id,
        ];
    }

    public function formResource(Product $product): array
    {
        $space = $product->productable;

        return [
            'id' => $space->id ?? null,
        ];
    }

    public function unassignSpaceFromProducts(): void
    {
        $products = Product::where('productable_type', Space::class)->get();

        foreach ($products as $product) {
            $product->productable_type = null;
            $product->productable_id = null;

            $product->save();
        }
    }

    /**
     * @return array<int, int>
     */
    private function nonAssignableTypeIds(): array
    {
        return app(SpaceService::class)->types()
            ->filter(fn ($type) => in_array($type->name, ['Country', 'City'], true))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
