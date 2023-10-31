<?php

namespace App\Listeners;

use App\Events\ModuleDeactivated;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ModuleService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UnassignModulePermissions implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(protected ModuleService $appModuleService)
    {}

    public function handle(ModuleDeactivated $event)
    {
        $moduleService = $this
            ->appModuleService
            ->getServiceClass($event->module->name);

        if (method_exists($moduleService, 'permissions')) {
            $permissionNames = $moduleService->permissions();

            $permissionModels = Permission::whereIn('name', $permissionNames)->get();

            Role::whereHas(
                'permissions',
                fn ($query) => $query->whereIn('name', $permissionNames)
            )
                ->get()
                ->each(function ($role) use ($permissionModels) {
                    $role->revokePermissionTo($permissionModels);
                });
        }
    }
}
