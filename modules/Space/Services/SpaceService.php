<?php

namespace Modules\Space\Services;

use Illuminate\Support\Collection;
use Modules\Space\Entities\Space;

class SpaceService
{
    public function spaceTree($parentId = null): array
    {
        return Space::with('children.children')
            ->when(is_null($parentId), function ($query) {
                $query->whereNull('parent_id');
            })
            ->when(!is_null($parentId), function ($query) use ($parentId) {
                $query->where('id', $parentId);
            })
            ->get()
            ->toArray();
    }

    public function parentOptions(array $parentIds = null): Collection
    {
        $parentOptions = collect();

        if (is_null($parentIds)) {
            $parentOptions = Space::whereIn('depth', [0, 1])
                ->orderBy('name')
                ->get(['id', 'name'])
                ->asOptions('id', 'name');

            $parentOptions->prepend(['id' => null, 'value' => 'None']);

        } else {

            $parentOptions = Space::whereIn('id', $parentIds)
                ->get(['id', 'name'])
                ->asOptions('id', 'name');
        }

        return $parentOptions;
    }
}
