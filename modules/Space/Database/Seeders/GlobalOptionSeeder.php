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
                'key' => 'space_type',
            ],
            [
                'name' => 'City',
                'key' => 'space_type',
            ],
            [
                'name' => 'Pitch',
                'key' => 'space_type',
            ],
        ];

        foreach ($options as $option) {
            GlobalOption::create($option);
        }
    }
}
