<?php

namespace Modules\Ecommerce\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EcommerceDatabaseBasicSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        $this->call("Modules\Ecommerce\Database\Seeders\PermissionSeeder");
        $this->call("Modules\Ecommerce\Database\Seeders\DefaultSeeder");
    }
}
