<?php

namespace Modules\FormBuilder\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class FormBuilderDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call("Modules\FormBuilder\Database\Seeders\PermissionSeeder");
        $this->call("Modules\FormBuilder\Database\Seeders\FieldGroupSeeder");
    }
}
