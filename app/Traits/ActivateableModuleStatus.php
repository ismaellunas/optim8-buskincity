<?php

namespace App\Traits;

use App\Models\Role;

trait ActivateableModuleStatus
{
    private function assignPermissionsToAdmin(): void
    {
        Role::where('name', config('permission.role_names.admin'))
            ->get()
            ->each(function ($adminRole) {
                foreach ($this->adminPermissions() as $permission) {
                    $adminRole->givePermissionTo($permission);
                }
            });
    }

    public function activated(): void
    {
        $this->assignPermissionsToAdmin();
    }
}
