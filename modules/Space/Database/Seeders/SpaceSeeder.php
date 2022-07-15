<?php

namespace Modules\Space\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Space\Entities\Space;

class SpaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $country = Space::factory()->create([
            'name' => "Sweden",
            'depth' => 0,
        ]);

        $city = Space::factory()->create([
            'name' => "Stockholm",
            'depth' => 1,
            'parent_id' => $country->id,
        ]);

        Space::factory()->create([
            'name' => "Town Park",
            'depth' => 2,
            'parent_id' => $city->id,
        ]);

        Space::factory()->create([
            'name' => "City Garden",
            'depth' => 2,
            'parent_id' => $city->id,
        ]);
    }
}
