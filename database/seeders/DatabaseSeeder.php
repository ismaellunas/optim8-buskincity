<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);

        // Wildcard permissions
        $wildcardPermissions = Permission::where('name', 'LIKE', '%.*')->get();

        $administratorRole = Role::whereName('Administrator')->first();
        $administratorRole->syncPermissions($wildcardPermissions);

        // System Permissions
        $systemPermissions = Permission::where('name', 'LIKE', 'system.%')->get();
        foreach ($systemPermissions as $permission) {
            $administratorRole->givePermissionTo($permission);
        }

        $superAdminUser = User::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'super.administrator@sdbagency.com',
        ]);

        $superAdminUser->assignRole(config('permission.super_admin_role'));

        $adminUser = User::factory()
            ->create([
                'name' => 'Administrator',
                'email' => 'admin@sdbagency.com',
            ]);

        $adminUser->assignRole('Administrator');

        $category = Category::factory()
            ->hasTranslations(1, ['name' => 'News'])
            ->create();

        Post::factory()
            ->count(2)
            ->for($adminUser, 'author')
            ->state(new Sequence(
                ['status' => Post::STATUS_DRAFT],
                ['status' => Post::STATUS_PUBLISHED],
            ))
            ->hasAttached($category)
            ->fakeContent()
            ->create();

        Page::factory()
            ->hasTranslations(1)
            ->create();

        $settings = [
            [
                "key" => "color_primary",
                "display_name" => "Primary Color",
                "value" => "#00d1b2",
                "group" => "theme_color"
            ],
            [
                "key" => "color_link",
                "display_name" => "Link Color",
                "value" => "#485fc7",
                "group" => "theme_color"
            ],
            [
                "key" => "color_info",
                "display_name" => "Info Color",
                "value" => "#3e8ed0",
                "group" => "theme_color"
            ],
            [
                "key" => "color_success",
                "display_name" => "Success Color",
                "value" => "#48c78e",
                "group" => "theme_color"
            ],
            [
                "key" => "color_warning",
                "display_name" => "Warning Color",
                "value" => "#ffe08a",
                "group" => "theme_color"
            ],
            [
                "key" => "color_danger",
                "display_name" => "Danger Color",
                "value" => "#f14668",
                "group" => "theme_color"
            ]
        ];

        foreach ($settings as $setting) {
            Setting::factory()->create($setting);
        }
    }
}
