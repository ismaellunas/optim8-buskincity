<?php

namespace Modules\Space\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class SpaceDatabaseBasicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call("Modules\Space\Database\Seeders\PermissionSeeder");
    }
}
