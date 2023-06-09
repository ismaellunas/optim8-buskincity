<?php

namespace Database\Seeders;

use App\Services\GlobalOptionService;
use App\Models\Form;
use App\Models\FieldGroup;
use App\Services\SettingService;
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
        $imageMimes = ['image'];
        $imageAndVideoMimes = ['image', 'video'];
        $maxFileSize = SettingService::maxFileSize();

        $about = [
            "key" => "about",
            "name" => "About",
            "order" => 1,
            "setting" => [
                "visibility" => [],
                "locations" => [
                    [
                        "name" => 'admin.profile.show',
                        "visibility" => [
                            'not_in_roles' => [
                                'Performer'
                            ]
                        ]
                    ],
                    [
                        "name" => 'admin.users.edit',
                        "visibility" => [
                            'not_in_roles' => [
                                'Performer'
                            ]
                        ]
                    ],
                ],
                "button" => [
                    "text" => "Update",
                    "position" => null,
                ]
            ],
            "fieldGroups" => [
                [
                    "title" => "About",
                    "order" => 1,
                    "fields" => [
                        [
                            "type" => "Textarea",
                            "name" => "short_description",
                            "label" => "Short Description",
                            "placeholder" => "Short description about yourself",
                            "notes" => [],
                            "default_value" => [],
                            "readonly" => false,
                            "disabled" => false,
                            "maxlength" => "",
                            "rows" => "",
                            "validation" => [
                                "rules" => [
                                    "max" => 1000
                                ],
                                "messages" => []
                            ],
                            "visibility" => [],
                            "translated" => true,
                        ],
                    ]
                ]
            ],
        ];

        $aboutYou = [
            "key" => "about_you",
            "name" => "About you",
            "order" => 2,
            "setting" => [
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
                "button" => [
                    "text" => "Update",
                    "position" => null,
                ]
            ],
            "fieldGroups" => [
                [
                    "title" => "About you",
                    "order" => 1,
                    "fields" => [
                        [
                            "type" => "Select",
                            "name" => "discipline",
                            "label" => "Discipline",
                            "notes" => [],
                            "readonly" => false,
                            "disabled" => false,
                            "options" => app(GlobalOptionService::class)->getDisciplineOptions(),
                            "validation" => [
                                "rules" => [
                                    "required" => true,
                                ],
                                "messages" => []
                            ],
                            "visibility" => [],
                            "translated" => false,
                        ],
                        [
                            "type" => "Text",
                            "name" => "stage_name",
                            "label" => "Stage name",
                            "placeholder" => "Enter your stage name",
                            "notes" => [],
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
                            "visibility" => [],
                            "translated" => false,
                        ],
                        [
                            "type" => "Textarea",
                            "name" => "short_bio",
                            "label" => "Short bio",
                            "placeholder" => "Short description about yourself",
                            "notes" => [],
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
                            "notes" => [],
                            "default_value" => [],
                            "readonly" => false,
                            "disabled" => false,
                            "maxlength" => "",
                            "rows" => "",
                            "validation" => [
                                "rules" => [
                                    "required" => true,
                                    "max" => 2000
                                ],
                                "messages" => []
                            ],
                            "visibility" => [],
                            "translated" => true,
                        ],
                    ]
                ],
                [
                    "title" => "Address & contact information",
                    "order" => 2,
                    "fields" => [
                        [
                            "type" => "Phone",
                            "name" => "phone",
                            "label" => "Phone",
                            "placeholder" => "Enter your phone number",
                            "notes" => [],
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
                            "notes" => [],
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
                            "notes" => [],
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
                            "notes" => [],
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
                            "notes" => [],
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
                ],
                [
                    "title" => "Social media",
                    "order" => 3,
                    "fields" => [
                        [
                            "type" => "Text",
                            "name" => "facebook",
                            "label" => "Facebook",
                            "placeholder" => "Your Facebook URL",
                            "notes" => [
                                'E.g: https://www.facebook.com/buskincity'
                            ],
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
                            "notes" => [
                                'E.g: https://twitter.com/BuskinCity'
                            ],
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
                            "notes" => [
                                'E.g: https://www.instagram.com/buskincity/'
                            ],
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
                            "notes" => [
                                'E.g: https://www.youtube.com/c/BuskinCity'
                            ],
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
                            "notes" => [
                                'E.g: https://www.tiktok.com/@buskincity'
                            ],
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
                ],
                [
                    "title" => "Media",
                    "order" => 4,
                    "fields" => [
                        [
                            "type" => "Video",
                            "name" => "promotional_video",
                            "label" => "Promotional video",
                            "placeholder" => "Youtube/Vimeo Video URL",
                            "notes" => [
                                'E.g: https://vimeo.com/553766867'
                            ],
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
                            "notes" => [
                                'Recommended dimension: ' . config('constants.recomended_dimensions.cover') . '.',
                            ],
                            "readonly" => false,
                            "disabled" => false,
                            "max_file_number" => 1,
                            "min_file_number" => 0,
                            "is_multiple_upload" => false,
                            "validation" => [
                                "rules" => [
                                    "mimes" => $imageMimes,
                                    "max" => $maxFileSize,
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
                            "notes" => [],
                            "readonly" => false,
                            "disabled" => false,
                            "max_file_number" => 5,
                            "min_file_number" => 0,
                            "is_multiple_upload" => true,
                            "validation" => [
                                "rules" => [
                                    "mimes" => $imageAndVideoMimes,
                                    "max" => $maxFileSize,
                                ],
                                "messages" => []
                            ],
                            "visibility" => [
                                "roles" => ["Performer"]
                            ],
                            "translated" => false,
                        ],
                    ]
                ]
            ]
        ];

        $this->syncForm($about);
        $this->syncForm($aboutYou);
    }

    private function syncForm($data): void
    {
        $form = Form::updateOrCreate(
            [
                'key' => $data['key'],
            ],
            [
                'name' => $data['name'],
                'order' => $data['order'],
                'setting' => $data['setting'],
            ]
        );

        foreach ($data['fieldGroups'] as $fieldGroup) {
            FieldGroup::updateOrCreate(
                [
                    'title' => $fieldGroup['title'],
                    'form_id' => $form->id,
                ],
                [
                    'order' => $fieldGroup['order'],
                    'fields' => $fieldGroup['fields'],
                ]
            );
        }
    }
}
