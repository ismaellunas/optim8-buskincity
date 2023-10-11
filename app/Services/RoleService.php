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
        $moduleService = app(ModuleService::class);

        $modulePermissionGroups = $moduleService->getAllPermissionGroups();
        $disabledModuleNames = $moduleService->getAllDisabledNames();

        return Permission::get(['id', 'name'])
            ->map(function ($permission) use ($modulePermissionGroups) {
                $isAll = Str::endsWith($permission->name, '*');
                $groupTitle = Str::beforeLast($permission->name, '.');
                $module = null;

                $findGroup = $modulePermissionGroups->firstWhere('group', $groupTitle);

                if ($findGroup) {
                    $module = $findGroup['module'];
                    $prefix = Str::snake($module).'_term';
                    $key = ":{$prefix}.{$findGroup['group']}";

                    $groupTitleTerm = __($key);

                    if ($groupTitleTerm !== Str::replace(":", "", $key)) {
                        $groupTitle = $groupTitleTerm;
                    }
                }

                return [
                    'id' => $permission->id,
                    'value' => $permission->name,
                    'isAll' => $isAll,
                    'title' => $isAll ? 'All' : Str::of(Str::afterLast($permission->name, '.'))->title()->replace('_', ' '),
                    'module' => $module,
                    'groupTitle' => Str::of($groupTitle)
                        ->title()
                        ->replace('_', ' ')
                        ->__toString(),
                ];
            })
            ->filter(function ($permission) use ($disabledModuleNames) {
                return ! in_array($permission['module'], $disabledModuleNames);
            })
            ->groupBy('groupTitle')
            ->all();
    }
}
