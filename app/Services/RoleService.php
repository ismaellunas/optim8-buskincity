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

    private function getPermissionGroups(): Collection
    {
        $appModuleService = app(ModuleService::class);

        $permissionGroups = [];
        $moduleNames = $appModuleService->getAllEnabledNames();

        foreach ($moduleNames as $moduleName) {
            $moduleService = $appModuleService->getServiceClass($moduleName);

            if (method_exists($moduleService, 'permissions')) {
                $moduleName = Str::snake($moduleService->getName());

                $groups = $moduleService
                    ->permissions()
                    ->map(function ($permission) {
                        return Str::of(Str::beforeLast($permission, '.'))
                            ->__toString();
                    })
                    ->unique()
                    ->values()
                    ->all();

                foreach ($groups as $group) {
                    $permissionGroups[] = [
                        'module' => $moduleName,
                        'group' => $group
                    ];
                }
            }
        }

        return collect($permissionGroups);
    }


    public function getPermissionOptions(): array
    {
        $modulePermissionGroups = $this->getPermissionGroups();

        return Permission::get(['id', 'name'])
            ->map(function ($permission) use ($modulePermissionGroups) {
                $isAll = Str::endsWith($permission->name, '*');

                $groupTitle = Str::of(Str::beforeLast($permission->name, '.'))
                    ->title()
                    ->replace('_', ' ')
                    ->__toString();

                $foundGroup = $modulePermissionGroups->firstWhere('group', Str::snake($groupTitle));

                if ($foundGroup) {
                    $key = ":{$foundGroup['module']}_term.{$foundGroup['group']}";

                    $groupTitleTerm = __($key);

                    if ($groupTitleTerm !== Str::replace(":", "", $key)) {
                        $groupTitle = Str::title($groupTitleTerm);
                    }
                }

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
