<?php

namespace Modules\FormBuilder\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\FormBuilder\Entities\FieldGroup;

class FieldGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $data = [
            'name' => 'Dummy Form',
            'title' => 'dummy_form', // Key
            'type' => FieldGroup::TYPE,
            'data' => [
                'name' => null,
                'title' => null,
                'order' => null,
                'visibility' => [],
                'location' => [],
                'fields' => [],
            ],
        ];

        FieldGroup::create($data);
    }
}
