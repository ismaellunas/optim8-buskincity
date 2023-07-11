<?php

namespace Modules\Ecommerce\Database\Seeders;

class EcommerceDatabaseSeeder extends EcommerceDatabaseBasicSeeder
{
    public function run()
    {
        parent::run();

        $this->call("Modules\Ecommerce\Database\Seeders\ProductSeeder");
        $this->call("Modules\Ecommerce\Database\Seeders\ScheduleSeeder");
        $this->call("Modules\Ecommerce\Database\Seeders\OrderSeeder");
    }
}
