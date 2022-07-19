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

        $countries = [
            [
                'name' => "Sweden",
                'children' => [
                    [
                        'name' => "Stockholm",
                        'children' => [
                            [
                                'name' => "Town Park",
                            ],
                            [
                                'name' => "City Garden",
                            ]
                        ],
                    ],
                ],
            ],
            [
                'name' => "United Kingdom",
                'children' => [
                    [
                        'name' => "London",
                        'children' => [
                            [
                                'name' => "London Town Hall",
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => "Singapore",
                'children' => [
                    [
                        'name' => "Jurong Bird Park",
                    ],
                    [
                        'name' => "Flower Dome",
                    ],
                    [
                        'name' => "Botanical Garden Singapore",
                    ],
                    [
                        'name' => "Merlion Park",
                    ],
                    [
                        'name' => "Marina Bay Sands",
                    ],
                    [
                        'name' => "Cloud Forest",
                    ],
                ],
            ],
        ];

        foreach ($countries as $country) {
            Space::create($country);
        }
    }
}
