<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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

    private function getAllPermissionGroups(): Collection
    {
        $permissionGroups = collect();

        $appModuleService = app(ModuleService::class);

        $moduleNames = $appModuleService->allModules()
            ->where('is_manageable', true)
            ->pluck('name');

        foreach ($moduleNames as $moduleName) {
            $moduleService = $appModuleService->getServiceClass($moduleName);

            if (method_exists($moduleService, 'permissions')) {
                $groups = $moduleService
                    ->permissions()
                    ->map(function ($permission) {
                        return Str::beforeLast($permission, '.');
                    })
                    ->unique()
                    ->values()
                    ->all();

                foreach ($groups as $group) {
                    $permissionGroups->push([
                        'module' => $moduleService->getName(),
                        'group' => $group
                    ]);
                }
            }
        }

        return $permissionGroups;
    }

    public function getPermissionOptions(): array
    {
        $moduleService = app(ModuleService::class);

        $modulePermissionGroups = $this->getAllPermissionGroups();

        $disabledModuleNames = $moduleService->modules(false)->pluck('name');

        return Permission::get(['id', 'name'])
            ->map(function ($permission) use ($modulePermissionGroups) {
                $isAll = Str::endsWith($permission->name, '*');
                $groupTitle = Str::beforeLast($permission->name, '.');
                $module = null;

                $foundGroup = $modulePermissionGroups->firstWhere('group', $groupTitle);

                if ($foundGroup) {
                    $module = $foundGroup['module'];
                    $prefix = Str::snake($module).'_term';
                    $key = ":{$prefix}.{$foundGroup['group']}";

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
                return $disabledModuleNames->doesntContain($permission['module']);
            })
            ->groupBy('groupTitle')
            ->all();
    }
}
