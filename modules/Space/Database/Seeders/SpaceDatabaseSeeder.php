<?php

namespace Modules\Space\Database\Seeders;

class SpaceDatabaseSeeder extends SpaceDatabaseBasicSeeder
{
    public function run()
    {
        parent::run();

        $this->call("Modules\Space\Database\Seeders\GlobalOptionSeeder");
        $this->call("Modules\Space\Database\Seeders\SpaceSeeder");
        $this->call("Modules\Space\Database\Seeders\MenuSeeder");
    }
}
