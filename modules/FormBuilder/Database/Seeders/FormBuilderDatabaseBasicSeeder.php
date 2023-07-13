<?php

namespace Modules\FormBuilder\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class FormBuilderDatabaseBasicSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();

        $this->call(PermissionSeeder::class);
        $this->call(AutomateUserCreationSettingSeeder::class);
    }
}
