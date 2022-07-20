<?php

namespace Modules\Space\Database\Seeders;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
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

        $title = 'Sweden Space';
        $swedenPage = Page::factory()
            ->hasTranslations(1, [
                'title' => $title,
                'slug' => Str::slug($title),
                'status' => PageTranslation::STATUS_PUBLISHED,
                'data' => [
                    "structures" => [],
                    "entities" => [],
                    "media" => []
                ],
            ])
            ->create();

        $countries = [
            [
                'name' => "Sweden",
                'page_id' => $swedenPage->id,
                'is_page_enabled' => true,
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
