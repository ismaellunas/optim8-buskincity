<?php

namespace Database\Seeders;

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
    }
}
