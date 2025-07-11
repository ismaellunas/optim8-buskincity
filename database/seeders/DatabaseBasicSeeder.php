<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseBasicSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ModuleSeeder::class,
            GlobalOptionSeeder::class,
            CountrySeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            SettingSeeder::class,
            LanguageSeeder::class,
            TranslationSeeder::class,
            UserAndPermissionSeeder::class,
            LanguageSettingSeeder::class,
            MenuBasicSeeder::class,
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
