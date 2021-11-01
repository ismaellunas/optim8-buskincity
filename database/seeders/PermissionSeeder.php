<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'page.*',
            'page.browse',
            'page.read',
            'page.edit',
            'page.add',
            'page.delete',

            'post.*',
            'post.browse',
            'post.read',
            'post.edit',
            'post.add',
            'post.delete',

            'category.*',
            'category.browse',
            'category.read',
            'category.edit',
            'category.add',
            'category.delete',

            'media.*',
            'media.browse',
            'media.read',
            'media.edit',
            'media.add',
            'media.delete',

            'user.*',
            'user.browse',
            'user.read',
            'user.edit',
            'user.add',
            'user.delete',

            'system.dashboard',

            /*  BREAD:
            'browse',
            'read',
            'edit',
            'add',
            'delete',
             */
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
