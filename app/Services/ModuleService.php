<?php

namespace App\Services;

use App\Contracts\ToggleableModuleStatusInterface;
use App\Entities\Caches\ModuleCache;
use App\Models\Module;
use App\Traits\HasCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ModuleService
{
    use HasCache;

    public function allModules(): Collection
    {
        $key = 'modules';

        return app(ModuleCache::class)->remember($key, function () {
            return Module::get(['name', 'title', 'navigations', 'is_active', 'is_manageable', 'order']);
        });
    }

    public function modules(bool $isActive = true, bool $isManagable = true): Collection
    {
        return $this->staticRemember(
            'managable_active_modules',
            fn () => $this
                ->allModules()
                ->where('is_manageable', $isActive)
                ->where('is_active', $isManagable)
        );
    }

    public function isModuleActive(string $moduleName): bool
    {
        $moduleName = Str::studly($moduleName);

        return $this
            ->allModules()
            ->contains(fn ($module) => $module->is_active && $module->name == $moduleName);
    }

    public function getAllEnabledNames(): array
    {
        return $this->staticRemember(
            'all_enabled_module_names',
            fn () => $this->modules()->pluck('name')->all()
        );
    }

    public static function getServiceClass(mixed $module): mixed
    {
        $moduleName = is_object($module) ? $module->name : Str::studly($module);

        $moduleService = '\\Modules\\'.$moduleName.'\\ModuleService';

        if (class_exists($moduleService)) {
            return app($moduleService);
        }

        return null;
    }

    public function getRecords(
        string $term = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $records = Module::orderBy('order')
            ->when($term, function ($query) use ($term) {
                $query->where('title', 'ILIKE', '%'.$term.'%');
            })
            ->where('is_manageable', true)
            ->select('id', 'title', 'order', 'is_active')
            ->paginate($perPage);

        $records->getCollection()->each->append(['displayStatus', 'displayTitle']);

        return $records;
    }

    public function getRecord(Module $module)
    {
        return $module;
    }

    public function updateGeneral(Module $module, $inputs): bool
    {
        $module->fill($inputs);

        return $module->save();
    }

    public function tabs(Module $module): array
    {
        $baseRouteName = 'admin.settings.modules.';

        return [
            [
                'title' => __('General'),
                'url' => route($baseRouteName.'edit', $module),
            ],
            [
                'title' => __('Navigations'),
                'url' => route($baseRouteName.'navigations.edit', $module),
            ],
        ];
    }

    public function activate(Module $module): bool
    {
        $module->is_active = true;
        return $module->save();
    }

    public function deactivate(Module $module): bool
    {
        $module->is_active = false;
        return $module->save();
    }

    public function onActivated(Module $module)
    {
        $moduleService = $this->getServiceClass($module);

        if ($moduleService instanceof ToggleableModuleStatusInterface) {
            $moduleService->activated();
        }
    }

    public function onDeactivated(Module $module)
    {
        $moduleService = $this->getServiceClass($module);

        if (
            $moduleService instanceof ToggleableModuleStatusInterface
            && $eventClass = $moduleService->deactivationEventClass()
        ) {
            $eventClass::dispatch($module);
        }
    }

    public function getNavigations(Module $module): array
    {
        $moduleService = $this->getServiceClass($module);

        if ($moduleService) {
            return $moduleService->navigations();
        }

        return [];
    }

    public function updateNavigations(Module $module, array $navigations): void
    {
        $module->navigations = $navigations;
        $module->save();
    }

    public function updateOrders(Collection $orderedInputs): void
    {
        $modules = Module::whereIn('id', $orderedInputs->pluck('id') )
            ->get(['id', 'order']);

        foreach ($modules as $module) {
            $orderedInput = $orderedInputs->firstWhere('id', '=', $module->id);

            $module->order = $orderedInput['order'];

            $module->save();
        }
    }

    public function getPermissionGroups(): Collection
    {
        $permissionGroups = [];
        $moduleNames = $this->getAllEnabledNames();

        foreach ($moduleNames as $moduleName) {
            $className = "\\Modules\\{$moduleName}\\ModuleService";

            if (
                class_exists($className)
                && method_exists($className, 'permissions')
            ) {
                $moduleName = Str::snake($className::getName());

                $groups = collect($className::permissions()->all())
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

    public function deactivationMessages(Module $module): array
    {
        $moduleService = $this->getServiceClass($module);
        $messages = [];

        if (
            $moduleService
            && $moduleService instanceof ToggleableModuleStatusInterface
        ) {
            $messages = $moduleService->deactivationMessages();
        }

        return $messages;
    }
}
