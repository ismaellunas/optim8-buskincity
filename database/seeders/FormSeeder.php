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

        $aboutYou = [
            "name" => "about_you",
            "title" => "About you",
            "order" => 1,
            "visibility" => [],
            "locations" => [
                [
                    "name" => 'admin.profile.show',
                    "visibility" => [
                        "roles" => [
                            "Performer"
                        ]
                    ]
                ],
                [
                    "name" => 'admin.users.edit',
                    "visibility" => [
                        "roles" => [
                            "Super Administrator",
                            "Administrator"
                        ]
                    ]
                ],
            ],
            "fields" => [
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
                    "label" => "Stage name",
                    "placeholder" => "Enter your stage name",
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
                    "label" => "Short bio",
                    "placeholder" => "Short description about yourself",
                    "note" => null,
                    "default_value" => [],
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => "",
                    "rows" => "",
                    "validation" => [
                        "rules" => [
                            "required",
                            "max: 3000"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => true,
                ],
                "long_bio" => [
                    "type" => "Textarea",
                    "label" => "Long bio",
                    "placeholder" => "Long description about yourself",
                    "note" => null,
                    "default_value" => [],
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => "",
                    "rows" => "",
                    "validation" => [
                        "rules" => [
                            "required",
                            "max: 65535"
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
            "title" => "Address & contact information",
            "order" => 2,
            "visibility" => [],
            "locations" => [
                [
                    "name" => 'admin.profile.show',
                    "visibility" => [
                        "roles" => [
                            "Performer"
                        ]
                    ]
                ],
                [
                    "name" => 'admin.users.edit',
                    "visibility" => [
                        "roles" => [
                            "Super Administrator",
                            "Administrator"
                        ]
                    ]
                ],
            ],
            "fields" => [
                "phone" => [
                    "type" => "Phone",
                    "label" => "Phone",
                    "placeholder" => "Enter your phone number",
                    "note" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [
                        ],
                        "messages" => [],
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                "address" => [
                    "type" => "Text",
                    "label" => "Address",
                    "placeholder" => "Street address",
                    "note" => null,
                    "default_value" => [],
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => "",
                    "validation" => [
                        "rules" => [
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
                    "column" => true,
                    "validation" => [
                        "rules" => [
                            "max:28",
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                "postcode" => [
                    "type" => "Postcode",
                    "label" => "Postcode",
                    "placeholder" => "Postcode",
                    "note" => null,
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "column" => true,
                    "validation" => [
                        "rules" => [
                            "max:10",
                            //"digits_between:0,10"
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
            "name" => "social_media",
            "title" => "Social media",
            "order" => 3,
            "visibility" => [],
            "locations" => [
                [
                    "name" => 'admin.profile.show',
                    "visibility" => [
                        "roles" => [
                            "Performer",
                        ]
                    ]
                ],
                [
                    "name" => 'admin.users.edit',
                    "visibility" => [
                        "roles" => [
                            "Super Administrator",
                            "Administrator"
                        ]
                    ]
                ],
            ],
            "fields" => [
                "facebook" => [
                    "type" => "Text",
                    "label" => "Facebook",
                    "placeholder" => "Your Facebook URL",
                    "note" => 'E.g: https://www.facebook.com/buskincity',
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
                    "left_icon" => 'fa-brands fa-facebook',
                ],
                "twitter" => [
                    "type" => "Text",
                    "label" => "Twitter",
                    "placeholder" => "Your Twitter URL",
                    "note" => 'E.g: https://twitter.com/BuskinCity',
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
                    "left_icon" => 'fa-brands fa-twitter',
                ],
                "instagram" => [
                    "type" => "Text",
                    "label" => "Instagram",
                    "placeholder" => "Your Instagram URL",
                    "note" => 'E.g: https://www.instagram.com/buskincity/',
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
                    "left_icon" => 'fa-brands fa-instagram',
                ],
                "youtube" => [
                    "type" => "Text",
                    "label" => "Youtube",
                    "placeholder" => "Your Youtube URL",
                    "note" => 'E.g: https://www.youtube.com/c/BuskinCity',
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
                    "left_icon" => 'fa-brands fa-youtube',
                ],
                "tiktok" => [
                    "type" => "Text",
                    "label" => "TikTok",
                    "placeholder" => "Your TikTok URL",
                    "note" => 'E.g: https://www.tiktok.com/@buskincity',
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
                    "left_icon" => 'fa-brands fa-tiktok',
                ],
            ]
        ];

        $appearance = [
            "name" => "media",
            "title" => "Media",
            "order" => 4,
            "visibility" => [],
            "locations" => [
                [
                    "name" => 'admin.profile.show',
                    "visibility" => [
                        "roles" => [
                            "Performer"
                        ]
                    ]
                ],
                [
                    "name" => 'admin.users.edit',
                    "visibility" => [
                        "roles" => [
                            "Super Administrator",
                            "Administrator"
                        ]
                    ]
                ],
            ],
            "fields" => [
                "promotional_video" => [
                    "type" => "Video",
                    "label" => "Promotional video",
                    "placeholder" => "Youtube/Vimeo Video URL",
                    "note" => 'E.g: https://vimeo.com/553766867',
                    "default_value" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [
                            "max:128",
                            "url"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [
                        "roles" => ["Performer"]
                    ],
                    "translated" => false,
                    "left_icon" => 'fa-brands fa-vimeo',
                ],
                "cover_background_photo" => [
                    "type" => "FileDragDrop",
                    "label" => "Cover background photo",
                    "file_label" => "Choose an image",
                    "placeholder" => 'Drop files here...',
                    "note" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "max_file_number" => 1,
                    "min_file_number" => 0,
                    "max_file_size" => config('constants.one_megabyte') * 50,
                    "validation" => [
                        "rules" => [
                            "mimes:".$imageMimes,
                            "max:".config('constants.one_megabyte') * 50,
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                "gallery" => [
                    "type" => "FileDragDrop",
                    "label" => "Gallery",
                    "file_label" => "Choose an image",
                    "placeholder" => "Drop files here...",
                    "note" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "max_file_number" => 5,
                    "min_file_number" => 0,
                    "max_file_size" => config('constants.one_megabyte') * 50,
                    "validation" => [
                        "rules" => [
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
            ['title' => $aboutYou['name']],
            ['data' =>  $aboutYou]
        );

        FieldGroup::updateOrCreate(
            ['title' => $address['name']],
            ['data' => $address]
        );

        FieldGroup::updateOrCreate(
            ['title' => $promotional['name']],
            ['data' => $promotional]
        );

        FieldGroup::updateOrCreate(
            ['title' => $appearance['name']],
            ['data' => $appearance]
        );
    }
}
