<?php

namespace Modules\FormBuilder\Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\FormBuilder\ModuleService;
use Spatie\Permission\Models\Role;

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

        $permissions
            ->filter(fn ($permission) => Str::endsWith($permission, '.*'))
            ->all();

        $role = Role::findByName(config('permission.role_names.admin'));
        $role->givePermissionTo($permissions);
    }
}
