<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class RoleService
{
    public function getRecords(
        string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return Role::withoutSuperAdmin()
            ->when($term, function ($query) use ($term) {
                $query->where('name', 'ILIKE', '%'.$term.'%');
            })
            ->paginate($perPage);
    }

    public function getPermissionOptions(): array
    {
        return Permission::get(['id', 'name'])
            ->map(function ($permission) {
                $isAll = Str::endsWith($permission->name, '*');
                return [
                    'id' => $permission->id,
                    'value' => $permission->name,
                    'groupTitle' => Str::of(Str::beforeLast($permission->name, '.'))->title()->__toString(),
                    'isAll' => $isAll,
                    'title' => $isAll ? 'All' : Str::of(Str::afterLast($permission->name, '.'))->title(),
                ];
            })
            ->groupBy('groupTitle')
            ->all();
    }
}
