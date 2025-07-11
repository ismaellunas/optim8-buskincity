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
            'media.other_users',

            'user.*',
            'user.browse',
            'user.read',
            'user.edit',
            'user.add',
            'user.delete',

            'error_log.*',
            'error_log.browse',
            'error_log.read',
            'error_log.delete',

            'system.dashboard',
            'system.language',
            'system.translation',
            'system.theme',
            'system.payment',
            'system.log',
            'system.cookie_consent',

            'payment.management',

            'public_page.profile',

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
