<?php

namespace Database\Seeders;

use App\Services\CountryService;
use App\Models\FieldGroup;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imageMimes = implode(',', config('constants.extensions.image'));
        $imageAndVideoMimes = implode(',', array_merge(
            config('constants.extensions.image'),
            config('constants.extensions.video'),
        ));

        $countryOptions = app(CountryService::class)
            ->getCountryOptions()
            ->flatMap(function ($country) {
                return [
                    $country['id'] => $country['value']
                ];
            })
            ->all();

        $appearance = [
            "name" => "appearance",
            "title" => "Appearance",
            "order" => 1,
            "visibility" => [
                "roles" => ["Performer"]
            ],
            "locations" => [
                'admin.profile.show',
                'admin.users.edit',
            ],
            "fields" => [
                "top_background_picture" => [
                    "type" => "File",
                    "label" => "Top Background Picture",
                    "placeholder" => null,
                    "note" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "max_file_number" => 1,
                    "min_file_number" => 1,
                    "validation" => [
                        "rules" => [
                            "required",
                            "mimes:".$imageMimes,
                            "max:".config('constants.one_megabyte') * 50,
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
            ]
        ];

        $biodata = [
            "name" => "biodata",
            "title" => "Biodata",
            "order" => 2,
            "visibility" => [
                "roles" => ["Performer"]
            ],
            "locations" => [
                'admin.profile.show',
                'admin.users.edit',
            ],
            "fields" => [
                "phone" => [
                    "type" => "Phone",
                    "label" => "Phone",
                    "placeholder" => "Phone",
                    "note" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [
                            "required",
                        ],
                        "messages" => [],
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                "discipline" => [
                    "type" => "Select",
                    "label" => "Discipline",
                    "note" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "options" => [
                        "Acrobat" => "Acrobat",
                        "BalanceAct" => "BalanceAct",
                        "Clown" => "Clown",
                        "Dance-Acrobatic" => "Dance-Acrobatic",
                        "Dance/Break/Popping/Locking" => "Dance/Break/Popping/Locking",
                        "Escapologist" => "Escapologist",
                        "Juggler" => "Juggler",
                        "Magician" => "Magician",
                        "Multidisciplinary Circus/Variety" => "Multidisciplinary Circus/Variety",
                        "Musician/Singer/Band" => "Musician/Singer/Band",
                        "Music-Clown" => "Music-Clown",
                        "Music-Acrobat" => "Music-Acrobat",
                        "Performance" => "Performance",
                        "Pupeteer" => "Pupeteer",
                        "Stiltwalkers/Animation" => "Stiltwalkers/Animation",
                        "Visual Comedy" => "Visual Comedy",
                        "Other" => "Other",
                    ],
                    "validation" => [
                        "rules" => [
                            "required",
                        ],
                        "messages" => []
                    ],
                    "visibility" => [
                        "roles" => ["Performer"]
                    ],
                    "translated" => false,
                ],
                "stage_name" => [
                    "type" => "Text",
                    "label" => "Stage Name",
                    "placeholder" => "Stage Name",
                    "note" => null,
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [
                            "required",
                            "max: 128",
                        ],
                        "messages" => []
                    ],
                    "visibility" => [
                        "roles" => ["Performer"]
                    ],
                    "translated" => false,
                ],
                "short_bio" => [
                    "type" => "Textarea",
                    "label" => "Short Bio",
                    "placeholder" => "Short Bio",
                    "note" => null,
                    "default_value" => [],
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => "",
                    "rows" => "",
                    "validation" => [
                        "rules" => [
                            "required",
                            "max: 1000"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => true,
                ],
                "long_bio" => [
                    "type" => "Textarea",
                    "label" => "Long Bio",
                    "placeholder" => "Long Bio",
                    "note" => null,
                    "default_value" => [],
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => "",
                    "rows" => "",
                    "validation" => [
                        "rules" => [
                            "required",
                            "max: 2000"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => true,
                ],
            ]
        ];

        $address = [
            "name" => "address",
            "title" => "Address",
            "order" => 3,
            "visibility" => [
                "roles" => ["Performer"]
            ],
            "locations" => [
                'admin.profile.show',
                'admin.users.edit',
            ],
            "fields" => [
                "address" => [
                    "type" => "Textarea",
                    "label" => "Address",
                    "placeholder" => "Your Address",
                    "note" => null,
                    "default_value" => [],
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => "",
                    "rows" => "",
                    "validation" => [
                        "rules" => [
                            "required",
                            "max:255"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => true,
                ],
                "city" => [
                    "type" => "Text",
                    "label" => "City",
                    "placeholder" => "City",
                    "note" => null,
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [
                            "required",
                            "max:28",
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                "postcode" => [
                    "type" => "Text",
                    "label" => "Post Code",
                    "placeholder" => "Post Code",
                    "note" => null,
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [
                            "required",
                            "max:10",
                            "digits_between:0,10"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                "country" => [
                    "type" => "Select",
                    "label" => "Country",
                    "note" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "options" => $countryOptions,
                    "validation" => [
                        "rules" => [
                            "required"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
            ]
        ];

        $promotional = [
            "name" => "promotional",
            "title" => "",
            "order" => 4,
            "visibility" => [
                "roles" => ["Performer"]
            ],
            "locations" => [
                'admin.profile.show',
                'admin.users.edit',
            ],
            "fields" => [
                "facebook" => [
                    "type" => "Text",
                    "label" => "Facebook",
                    "placeholder" => "facebook URL",
                    "note" => null,
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => 128,
                    "validation" => [
                        "rules" => [
                            "max:128",
                            "url"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                "twitter" => [
                    "type" => "Text",
                    "label" => "Twitter",
                    "placeholder" => "twitter URL",
                    "note" => null,
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => 128,
                    "validation" => [
                        "rules" => [
                            "max:128",
                            "url"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                "instagram" => [
                    "type" => "Text",
                    "label" => "Instagram",
                    "placeholder" => "instagram URL",
                    "note" => null,
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => 128,
                    "validation" => [
                        "rules" => [
                            "max:128",
                            "url"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                "youtube" => [
                    "type" => "Text",
                    "label" => "Youtube",
                    "placeholder" => "youtube URL",
                    "note" => null,
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => 128,
                    "validation" => [
                        "rules" => [
                            "max:128",
                            "url"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                "tiktok" => [
                    "type" => "Text",
                    "label" => "Tiktok",
                    "placeholder" => "tiktok URL",
                    "note" => null,
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => 128,
                    "validation" => [
                        "rules" => [
                            "max:128",
                            "url"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                "promotional_video" => [
                    "type" => "Video",
                    "label" => "Promotional Video",
                    "placeholder" => "Youtube or Vimeo Link",
                    "note" => "Please add embed link for promotional video.",
                    "default_value" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [
                            "required",
                            "max:128",
                            "url"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [
                        "roles" => ["Performer"]
                    ],
                    "translated" => false,
                ],
                "gallery" => [
                    "type" => "File",
                    "label" => "Gallery",
                    "placeholder" => null,
                    "note" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "max_file_number" => 5,
                    "min_file_number" => 1,
                    "validation" => [
                        "rules" => [
                            "required",
                            "mimes:".$imageAndVideoMimes,
                            "max:".config('constants.one_megabyte') * 50,
                        ],
                        "messages" => []
                    ],
                    "visibility" => [
                        "roles" => ["Performer"]
                    ],
                    "translated" => false,
                ],
            ]
        ];

        FieldGroup::updateOrCreate(
            ['title' => $appearance['name']],
            ['data' => $appearance]
        );

        FieldGroup::updateOrCreate(
            ['title' => $biodata['name']],
            ['data' => $biodata]
        );

        FieldGroup::updateOrCreate(
            ['title' => $address['name']],
            ['data' => $address]
        );

        FieldGroup::updateOrCreate(
            ['title' => $promotional['name']],
            ['data' => $promotional]
        );
    }
}
