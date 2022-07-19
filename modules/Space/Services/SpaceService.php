<?php

namespace Modules\Space\Services;

use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Modules\Space\Entities\Space;

class SpaceService
{
    private function filterRootIds(array $ids)
    {
        $spaceIds = collect($ids);

        foreach ($ids as $spaceId) {
            $descendantIds = Space::whereDescendantOf($spaceId)
                ->get('id')
                ->pluck('id')
                ->all();

            $spaceIds = $spaceIds->diff($descendantIds);
        }

        return $spaceIds;
    }

    private function spaceRoots(array $ids = null): Collection
    {
        $roots = collect();
        $spaceIds = null;

        if (!is_null($ids)) {
            $spaceIds = $this->filterRootIds($ids);
        }

        Space::select(['id', 'parent_id', '_lft', '_rgt'])
            ->when(is_null($ids), function ($query) {
                $query->whereNull('parent_id');
            })
            ->when($spaceIds, function ($query, $spaceIds) {
                $query->whereIn('id', $spaceIds->all());
            })
            ->get()
            ->each(function ($space) use ($roots) {
                $tree = Space::select(['id', 'name', 'parent_id', '_lft', '_rgt'])
                    ->withDepth()
                    ->descendantsAndSelf($space);

                $roots->push($tree);
            });

        return $roots;
    }

    public function spaceTree(Authenticatable $user, int $parentId = null): Collection
    {
        if (! is_null($parentId)) {
            $spaceIds = [$parentId];
        } else {
            $spaceIds = null;

            if (! $user->can('space.viewAny')) {
                $spaceIds = $user->spaces->pluck('id')->all();
            }
        }

        $roots = $this->spaceRoots($spaceIds);

        return $roots->map(function ($root) {
            return $root->toTree()->first();
        });
    }

    public function parentOptions(
        Authenticatable $user,
        bool $isEmptyAllowed = true
    ): Collection {
        $spaceIds = null;

        if (! $user->can('space.viewAny')) {
            $spaceIds = $user->spaces->pluck('id')->all();
        }

        $options = collect();

        if ($isEmptyAllowed) {
            $options->push(['id' => null, 'value' => __('None')]);
        }

        $roots = $this->spaceRoots($spaceIds);

        $roots->each(function ($root) use ($options) {
            foreach ($root as $space) {
                if ($space->depth <= 1) {
                    $options->push([
                        'id' => $space->id,
                        'value' => $space->name,
                    ]);
                }
            }
        });

        return $options;
    }

    public function managers(
        string $term = null,
        array $excludedIds = [],
        int $limit = 15
    ): Collection {

        return User::available()
            ->backend()
            ->notInRoleNames([config('permission.super_admin_role')])
            ->when($term, function ($query, $term) {
                $query->search($term);
            })
            ->when($excludedIds, function ($query, $excludedIds) {
                $query->whereNotIn('id', $excludedIds);
            })
            ->limit($limit)
            ->get([
                'id',
                'first_name',
                'last_name',
            ])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'value' => $user->fullName,
                ];
            });
    }
}
