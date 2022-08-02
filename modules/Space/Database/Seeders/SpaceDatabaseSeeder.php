<?php

namespace Modules\Space\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SpaceDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call("Modules\Space\Database\Seeders\GlobalOptionSeeder");
        $this->call("Modules\Space\Database\Seeders\SpaceSeeder");
        $this->call("Modules\Space\Database\Seeders\PermissionSeeder");
        $this->call("Modules\Space\Database\Seeders\MenuSeeder");
    }
}
