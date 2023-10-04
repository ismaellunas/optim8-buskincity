<?php

namespace App\Listeners;

use App\Events\ModuleDeactivated;
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
            $permissions = $moduleService->permissions();

            Role::whereHas(
                'permissions',
                fn ($query) => $query->whereIn('name', $permissions)
            )
                ->get()
                ->each(function ($role) use ($permissions) {
                    foreach ($permissions as $permission) {
                        $role->revokePermissionTo($permission);
                    }
                });
        }
    }
}
