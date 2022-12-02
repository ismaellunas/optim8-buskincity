<?php

namespace Modules\Space\Database\Seeders;

use App\Models\PageTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Space\Entities\Space;
use Modules\Space\Entities\Page;
use Modules\Space\Services\SpaceService;

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

        $typeOptions = app(SpaceService::class)->typeOptions();

        $countryTypeId = $typeOptions->firstWhere('value', 'Country')['id'] ?? null;
        $cityTypeId = $typeOptions->firstWhere('value', 'City')['id'] ?? null;
        $pitchTypeId = $typeOptions->firstWhere('value', 'Pitch')['id'] ?? null;

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
                'type_id' => $countryTypeId,
                'children' => [
                    [
                        'name' => "Stockholm",
                        'type_id' => $cityTypeId,
                        'children' => [
                            [
                                'name' => "Town Park",
                                'type_id' => $pitchTypeId,
                            ],
                            [
                                'name' => "City Garden",
                                'type_id' => $pitchTypeId,
                            ]
                        ],
                    ],
                ],
            ],
            [
                'name' => "United Kingdom",
                'type_id' => $countryTypeId,
                'children' => [
                    [
                        'name' => "London",
                        'type_id' => $cityTypeId,
                        'children' => [
                            [
                                'name' => "London Town Hall",
                                'type_id' => $pitchTypeId,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => "Singapore",
                'type_id' => $countryTypeId,
                'children' => [
                    [
                        'name' => "Jurong Bird Park",
                        'type_id' => $pitchTypeId,
                    ],
                    [
                        'name' => "Flower Dome",
                        'type_id' => $pitchTypeId,
                    ],
                    [
                        'name' => "Botanical Garden Singapore",
                        'type_id' => $pitchTypeId,
                    ],
                    [
                        'name' => "Merlion Park",
                        'type_id' => $pitchTypeId,
                    ],
                    [
                        'name' => "Marina Bay Sands",
                        'type_id' => $pitchTypeId,
                    ],
                    [
                        'name' => "Cloud Forest",
                        'type_id' => $pitchTypeId,
                    ],
                ],
            ],
        ];

        foreach ($countries as $country) {
            Space::create($country);
        }
    }
}
