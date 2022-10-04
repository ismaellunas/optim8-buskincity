<?php

namespace Modules\Space\Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Space\ModuleService;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $permissions = ModuleService::permissions();

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $wildcardPermissions = $permissions
            ->filter(fn ($permission) => Str::endsWith($permission, '.*'))
            ->all();

        $administratorRole = Role::findByName(config('permission.role_names.admin'));
        $administratorRole->givePermissionTo($wildcardPermissions);
    }
}
