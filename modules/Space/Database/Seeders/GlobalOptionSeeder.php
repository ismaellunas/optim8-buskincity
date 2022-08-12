<?php

namespace Modules\Space\Database\Seeders;

use App\Models\GlobalOption;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class GlobalOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $options = [
            [
                'name' => 'Country',
                'type' => 'space',
            ],
            [
                'name' => 'City',
                'type' => 'space',
            ],
            [
                'name' => 'Pitch',
                'type' => 'space',
            ],
        ];

        foreach ($options as $option) {
            GlobalOption::create($option);
        }
    }
}
