<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class CityAdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create role
        $role = Role::firstOrCreate([
            'name' => 'city_administrator',
            'guard_name' => 'web'
        ]);

        // Define permissions
        $permissions = [
            'system.dashboard',     // Required to access admin panel
            'city.manage_events',  // Can create/edit/delete events in their cities
            'city.view_reports',   // Can view reports for their cities
        ];

        // Create permissions
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
        }

        // Assign permissions to role
        $role->syncPermissions($permissions);
    }
}
