<?php

namespace Modules\FormBuilder\Database\Seeders;

class FormBuilderDatabaseSeeder extends FormBuilderDatabaseBasicSeeder
{
    public function run()
    {
        parent::run();

        $this->call(FormSeeder::class);
    }
}
