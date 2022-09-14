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
                [
                    "type" => "Select",
                    "name" => "discipline",
                    "label" => "Discipline",
                    "note" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "options" => [
                        [
                            "id" => "Acrobat",
                            "value" => "Acrobat",
                        ],
                        [
                            "id" => "BalanceAct",
                            "value" => "BalanceAct",
                        ],
                        [
                            "id" => "Clown",
                            "value" => "Clown",
                        ],
                        [
                            "id" => "Acrobatic",
                            "value" => "Acrobatic",
                        ],
                        [
                            "id" => "Dance/Break/Popping/Locking",
                            "value" => "Dance/Break/Popping/Locking",
                        ],
                        [
                            "id" => "Escapologist",
                            "value" => "Escapologist",
                        ],
                        [
                            "id" => "Juggler",
                            "value" => "Juggler",
                        ],
                        [
                            "id" => "Magician",
                            "value" => "Magician",
                        ],
                        [
                            "id" => "Multidisciplinary Circus/Variety",
                            "value" => "Multidisciplinary Circus/Variety",
                        ],
                        [
                            "id" => "Musician/Singer/Band",
                            "value" => "Musician/Singer/Band",
                        ],
                        [
                            "id" => "Music-Clown",
                            "value" => "Music-Clown",
                        ],
                        [
                            "id" => "Music-Acrobat",
                            "value" => "Music-Acrobat",
                        ],
                        [
                            "id" => "Performance",
                            "value" => "Performance",
                        ],
                        [
                            "id" => "Pupeteer",
                            "value" => "Pupeteer",
                        ],
                        [
                            "id" => "Stiltwalkers/Animation",
                            "value" => "Stiltwalkers/Animation",
                        ],
                        [
                            "id" => "Visual Comedy",
                            "value" => "Visual Comedy",
                        ],
                        [
                            "id" => "Other",
                            "value" => "Other",
                        ],
                    ],
                    "validation" => [
                        "rules" => [
                            "required" => true,
                        ],
                        "messages" => []
                    ],
                    "visibility" => [
                        "roles" => ["Performer"]
                    ],
                    "translated" => false,
                ],
                [
                    "type" => "Text",
                    "name" => "stage_name",
                    "label" => "Stage name",
                    "placeholder" => "Enter your stage name",
                    "note" => null,
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [
                            "required" => true,
                            "max" => 128,
                        ],
                        "messages" => []
                    ],
                    "visibility" => [
                        "roles" => ["Performer"]
                    ],
                    "translated" => false,
                ],
                [
                    "type" => "Textarea",
                    "name" => "short_bio",
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
                            "required" => true,
                            "max" => 1000
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => true,
                ],
                [
                    "type" => "Textarea",
                    "name" => "long_bio",
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
                            "required" => true,
                            "max" => 65535
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
                    "visibility" => []
                ],
                [
                    "name" => 'admin.users.edit',
                    "visibility" => []
                ],
            ],
            "fields" => [
                [
                    "type" => "Phone",
                    "name" => "phone",
                    "label" => "Phone",
                    "placeholder" => "Enter your phone number",
                    "note" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [],
                        "messages" => [],
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                [
                    "type" => "Text",
                    "name" => "address",
                    "label" => "Address",
                    "placeholder" => "Street address",
                    "note" => null,
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => "",
                    "validation" => [
                        "rules" => [
                            "max" => 255
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                [
                    "type" => "Text",
                    "name" => "city",
                    "label" => "City",
                    "placeholder" => "City",
                    "note" => null,
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "column" => true,
                    "validation" => [
                        "rules" => [
                            "max" => 28,
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                [
                    "type" => "Postcode",
                    "name" => "postcode",
                    "label" => "Postcode",
                    "placeholder" => "Postcode",
                    "note" => null,
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "column" => true,
                    "validation" => [
                        "rules" => [
                            "max" => 10,
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                [
                    "type" => "Country",
                    "name" => "country",
                    "label" => "Country",
                    "note" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [
                            "required" => true,
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
                [
                    "type" => "Text",
                    "name" => "facebook",
                    "label" => "Facebook",
                    "placeholder" => "Your Facebook URL",
                    "note" => 'E.g: https://www.facebook.com/buskincity',
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => 128,
                    "validation" => [
                        "rules" => [
                            "max" => 128,
                            "url" => true
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                    "left_icon" => 'fa-brands fa-facebook',
                ],
                [
                    "type" => "Text",
                    "name" => "twitter",
                    "label" => "Twitter",
                    "placeholder" => "Your Twitter URL",
                    "note" => 'E.g: https://twitter.com/BuskinCity',
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => 128,
                    "validation" => [
                        "rules" => [
                            "max" => 128,
                            "url" => true
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                    "left_icon" => 'fa-brands fa-twitter',
                ],
                [
                    "type" => "Text",
                    "name" => "instagram",
                    "label" => "Instagram",
                    "placeholder" => "Your Instagram URL",
                    "note" => 'E.g: https://www.instagram.com/buskincity/',
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => 128,
                    "validation" => [
                        "rules" => [
                            "max" => 128,
                            "url" => true
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                    "left_icon" => 'fa-brands fa-instagram',
                ],
                [
                    "type" => "Text",
                    "name" => "youtube",
                    "label" => "Youtube",
                    "placeholder" => "Your Youtube URL",
                    "note" => 'E.g: https://www.youtube.com/c/BuskinCity',
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => 128,
                    "validation" => [
                        "rules" => [
                            "max" => 128,
                            "url" => true
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                    "left_icon" => 'fa-brands fa-youtube',
                ],
                [
                    "type" => "Text",
                    "name" => "tiktok",
                    "label" => "TikTok",
                    "placeholder" => "Your TikTok URL",
                    "note" => 'E.g: https://www.tiktok.com/@buskincity',
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => 128,
                    "validation" => [
                        "rules" => [
                            "max" => 128,
                            "url" => true
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
                [
                    "type" => "Video",
                    "name" => "promotional_video",
                    "label" => "Promotional video",
                    "placeholder" => "Youtube/Vimeo Video URL",
                    "note" => 'E.g: https://vimeo.com/553766867',
                    "default_value" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [
                            "max" => 128,
                            "url" => true
                        ],
                        "messages" => []
                    ],
                    "visibility" => [
                        "roles" => ["Performer"]
                    ],
                    "translated" => false,
                    "left_icon" => 'fa-brands fa-vimeo',
                ],
                [
                    "type" => "FileDragDrop",
                    "name" => "cover_background_photo",
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
                            "mimes" => $imageMimes,
                            "max" => config('constants.one_megabyte') * 50,
                        ],
                        "messages" => []
                    ],
                    "visibility" => [],
                    "translated" => false,
                ],
                [
                    "type" => "FileDragDrop",
                    "name" => "gallery",
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
                            "mimes" => $imageAndVideoMimes,
                            "max" => config('constants.one_megabyte') * 50,
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
