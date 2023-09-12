<?php

namespace Database\Seeders;

use Database\Seeders\Buskincity\PerformerApplicationSeeder;
use Database\Seeders\Buskincity\WidgetSeeder;
use Illuminate\Database\Seeder;

class DatabaseBuskincitySeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PerformerApplicationSeeder::class,
            WidgetSeeder::class,
        ]);
    }
}
