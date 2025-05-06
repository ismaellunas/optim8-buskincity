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
        $records = Role::withoutSuperAdmin()
            ->orderBy('id', 'DESC')
            ->when($term, function ($query) use ($term) {
                $query->where('name', 'ILIKE', '%'.$term.'%');
            })
            ->paginate($perPage);

        $records->getCollection()->transform(function ($record) {
            $record->can = [
                'delete' => auth()->user()->can('delete', $record)
            ];
            return $record;
        });

        return $records;
    }

    public function getPermissionOptions(): array
    {
        return Permission::get(['id', 'name'])
            ->map(function ($permission) {
                $isAll = Str::endsWith($permission->name, '*');

                $groupTitle = Str::of(Str::beforeLast($permission->name, '.'))
                    ->title()
                    ->replace('_', ' ')
                    ->__toString();

                return [
                    'id' => $permission->id,
                    'value' => $permission->name,
                    'groupTitle' => $groupTitle,
                    'isAll' => $isAll,
                    'title' => $isAll ? 'All' : Str::of(Str::afterLast($permission->name, '.'))->title()->replace('_', ' '),
                ];
            })
            ->groupBy('groupTitle')
            ->all();
    }
}
