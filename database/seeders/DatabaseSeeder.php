<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
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

        $wildcardPermissions = Permission::where('name', 'LIKE', '%.*')->get();

        $administratorRole = Role::whereName('Administrator')->first();
        $administratorRole->syncPermissions($wildcardPermissions);

        $superAdminUser = User::factory()->create([
            'first_name' => 'Super',
            'last_name' => 'Administrator',
            'email' => 'super.administrator@sdbagency.com',
        ]);

        $superAdminUser->assignRole(config('permission.super_admin_role'));

        $adminUser = User::factory()
            ->create([
                'first_name' => 'Admin',
                'last_name' => 'Administrator',
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
    }
}
