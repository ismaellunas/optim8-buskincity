<?php

namespace Database\Seeders;

use App\Models\{
    Category,
    Language,
    Menu,
    MenuItem,
    Page,
    Permission,
    Post,
    Role,
    User,
};
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use App\Services\LanguageService;

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
            CountrySeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            SettingSeeder::class,
            LanguageSeeder::class,
            TranslationSeeder::class,
            FormSeeder::class,
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
            'first_name' => 'Super',
            'last_name' => 'Administrator',
            'email' => 'super.administrator@biz.com',
        ]);

        $superAdminUser->assignRole(config('permission.super_admin_role'));

        $adminUser = User::factory()
            ->create([
                'first_name' => 'Admin',
                'last_name' => 'Administrator',
                'email' => 'admin@biz.com',
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

        Menu::factory()
            ->count(3)
            ->has(
                MenuItem::factory()
            )
            ->state(new Sequence(
                ['type' => Menu::TYPE_HEADER],
                ['type' => Menu::TYPE_FOOTER],
                ['type' => Menu::TYPE_SOCIAL_MEDIA],
            ))
            ->create([
                'locale' => config('app.fallback_locale')
            ]);

        $this->setActiveLanguages();
        $this->setDefaultLanguage();
    }

    private function setActiveLanguages()
    {
        $activeLanguages = [
            'en',
            'pt',
            'sv',
            'de',
        ];

        Language::whereIn('code', $activeLanguages)
            ->update(['is_active' => true]);
    }

    private function setDefaultLanguage()
    {
        $englishId = Language::where('code', 'en')->value('id');

        app(LanguageService::class)->setDefault($englishId);
    }
}
