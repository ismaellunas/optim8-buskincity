<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            GlobalOptionSeeder::class,
            CountrySeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            SettingSeeder::class,
            LanguageSeeder::class,
            TranslationSeeder::class,
            FormSeeder::class,
            CategorySeeder::class,
            UserAndPermissionSeeder::class,
            LanguageSettingSeeder::class,
            PageSeeder::class,
            PostSeeder::class,
            MenuSeeder::class,
            StripeSeeder::class,
        ]);

        $this->runAppIdDatabaseSeeder();
    }

    public function runAppIdDatabaseSeeder(): void
    {
        $appId = config('app.id');

        $className = $this->appIdDatabaseSeederClass($appId);

        if (
            class_exists($className)
            && is_subclass_of($className, '\Illuminate\Database\Seeder')
        ) {
            $this->call([$className]);
        }
    }

    private function appIdDatabaseSeederClass(string $appId): string
    {
        return "\\Database\\Seeders\\Database" . Str::studly($appId) . "Seeder";
    }
}
