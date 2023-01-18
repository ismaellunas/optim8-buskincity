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

        $countries = [
            [
                'name' => "Germany",
                'type_id' => $countryTypeId,
                'children' => [
                    [
                        'name' => "Berlin",
                        'latitude' => 52.5064968,
                        'longitude' => 13.1376701,
                        'type_id' => $cityTypeId,
                        'children' => [
                            [
                                'name' => "Pergamonmuseum",
                                'type_id' => $pitchTypeId,
                                'address' => "Bodestraße 1-3, 10178 Berlin, Germany",
                                'latitude' => 52.52119252486984,
                                'longitude' => 13.396902451332059,
                            ],
                            [
                                'name' => "Brandenburg Gate",
                                'type_id' => $pitchTypeId,
                                'address' => "Pariser Platz, 10117, G98H+G3",
                                'latitude' => 52.51733698723363,
                                'longitude' => 13.377799115385956,
                            ]
                        ],
                    ],
                    [
                        'name' => "Munich",
                        'type_id' => $cityTypeId,
                        'latitude' => 48.1433511,
                        'longitude' => 11.460595,
                        'children' => [
                            [
                                'name' => "Glyptothek",
                                'type_id' => $pitchTypeId,
                                'address' => "Königsplatz 3, 80333",
                                'latitude' => 48.14661473948528,
                                'longitude' => 11.56567738041144,
                            ],
                            [
                                'name' => "Deutsches Museum",
                                'type_id' => $pitchTypeId,
                                'address' => "Museumsinsel 1, 80538 4HHM+W9",
                                'latitude' => 48.13495937007051,
                                'longitude' => 11.5840630265014,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => "Sweden",
                'type_id' => $countryTypeId,
                'children' => [
                    [
                        'name' => "Stockholm",
                        'type_id' => $cityTypeId,
                        'latitude' => 52.3546449,
                        'longitude' => 4.8339213,
                        'children' => [
                            [
                                'name' => "Vasa Museum",
                                'type_id' => $pitchTypeId,
                                'address' => "Galärvarvsvägen 14, 115 21 83HR+6H",
                                'latitude' => 59.3286437970886,
                                'longitude' => 18.0915704926579,
                            ],
                            [
                                'name' => "Nobel Prize Museum",
                                'type_id' => $pitchTypeId,
                                'address' => "Stortorget 2, 103 16 83GC+48",
                                'latitude' => 59.3262018218294,
                                'longitude' => 18.071118483826,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => "Netherlands",
                'type_id' => $countryTypeId,
                'children' => [
                    [
                        'name' => "Amsterdam",
                        'type_id' => $cityTypeId,
                        'latitude' => 52.3543179,
                        'longitude' => 4.6168986,
                        'children' => [
                            [
                                'name' => "Stedelijk Museum Amsterdam",
                                'type_id' => $pitchTypeId,
                                'address' => "Museumplein 10, 1071 DJ, 9V5H+6W",
                                'latitude' => 52.3584304500091,
                                'longitude' => 4.87989154112885,
                            ],
                            [
                                'name' => "Rijksmuseum",
                                'type_id' => $pitchTypeId,
                                'address' => "Museumstraat 1, 1071 XX 9V5P+X3",
                                'latitude' => 52.3607373575951,
                                'longitude' => 4.88530846000091,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($countries as $country) {
            Space::create($country);
        }
    }
}
