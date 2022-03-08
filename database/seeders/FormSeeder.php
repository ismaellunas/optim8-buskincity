<?php

namespace Database\Seeders;

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
        $biodata = [
            "name" => "biodata",
            "title" => "",
            "order" => 1,
            "visibility" => [
            ],
            "locations" => [
                'admin.profile.show',
                'admin.users.edit',
            ],
            "fields" => [
                "gender" => [
                    "type" => "Radio",
                    "label" => "Gender",
                    "disabled" => false,
                    "default_value" => null,
                    "options" => [
                        "male" => "Male",
                        "female" => "Female",
                    ],
                    "validation" => [
                        "rules" => [
                            "required",
                        ],
                        "messages" => [],
                    ],
                    "translated" => false,
                ],
                "phone" => [
                    "type" => "Phone",
                    "label" => "Phone",
                    "placeholder" => "Phone",
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [
                        ],
                        "messages" => [],
                    ],
                    "translated" => false,
                ],
                "education" => [
                    "type" => "Select",
                    "label" => "Education",
                    "readonly" => false,
                    "disabled" => false,
                    "options" => [
                        "bachelor" => "Bachelor Degree",
                        "master" => "Master Degree",
                    ],
                    "validation" => [
                        "rules" => [],
                        "messages" => []
                    ],
                    "translated" => false,
                ],
                "next_of_kin" => [
                    "type" => "Text",
                    "label" => "Next Of Kin",
                    "placeholder" => "Next Of Kin",
                    "default_value" => [],
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => 64,
                    "validation" => [
                        "rules" => [
                            "required",
                            "max:64"
                        ],
                        "messages" => []
                    ],
                    "visibility" => [
                        "roles" => ["Administrator"]
                    ],
                    "translated" => true,
                ],
                "blood_type" => [
                    "type" => "Radio",
                    "label" => "Blood Type",
                    "disabled" => false,
                    "layout" => "horizontal",
                    "options" => [
                        "A" => "A",
                        "B" => "B",
                        "AB" => "AB",
                        "O" => "O",
                    ],
                    "validation" => [
                        "rules" => [
                            "required",
                        ],
                        "messages" => [],
                    ],
                    "visibility" => [
                        "roles" => ["Administrator"]
                    ],
                    "translated" => false,
                ],

                "about_me" => [
                    "type" => "Textarea",
                    "label" => "About Me",
                    "placeholder" => "How to make the world see you.",
                    "default_value" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => "",
                    "rows" => "",
                    "validation" => [
                        "rules" => [],
                        "messages" => []
                    ],
                    "visibility" => [],
                ],

                "facebook" => [
                    "type" => "Text",
                    "label" => "Facebook",
                    "placeholder" => "facebook url",
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
                ],

                "instagram" => [
                    "type" => "Text",
                    "label" => "Instagram",
                    "placeholder" => "instagram URL",
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
                ],

                "skills" => [
                    "type" => "CheckboxGroup",
                    "label" => "Skills",
                    "disabled" => false,
                    "readonly" => false,
                    "default_value" => [],
                    "is_raw" => false,
                    "layout" => "horizontal",
                    "options" => [
                        "actor" => "Actor",
                        "choreographer" => "Choreographer",
                        "circus_performer" => "Circus Performer",
                        "comedian" => "Comedian",
                        "dancer" => "Dancer",
                        "magician" => "Magician",
                        "musician" => "Musician",
                        "singer" => "Singer",
                        "stuntman" => "Stuntman",
                    ],
                    "validation" => [
                        "rules" => [],
                        "messages" => [],
                    ],
                    "translated" => false,
                ],

                "criminal_record" => [
                    "type" => "Textarea",
                    "label" => "Criminal Record",
                    "placeholder" => "...",
                    "default_value" => [],
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => "",
                    "rows" => "",
                    "validation" => [
                        "rules" => [],
                        "messages" => []
                    ],
                    "visibility" => [
                        "roles" => ['Administrator']
                    ],
                    "translated" => true,
                ],
                "video" => [
                    "type" => "Video",
                    "label" => "Performance Video",
                    "default_value" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [],
                        "messages" => []
                    ],
                    "visibility" => [
                    ],
                    "translated" => false,
                ],

                "term_and_condition" => [
                    "type" => "Checkbox",
                    "label" => "Term and Condition",
                    "text" => "I agree to the <a href='/' target='_blank'>terms and conditions</a>",
                    "disabled" => false,
                    "true_value" => true,
                    "false_value" => false,
                    "default_value" => false,
                    "is_raw" => true,
                    "is_boolean" => true,
                    "validation" => [
                        "rules" => [
                            "required",
                            "accepted"
                        ],
                        "messages" => [],
                    ],
                    "translated" => false,
                ],
            ]
        ];

        $address = [
            "name" => "address",
            "title" => "Address",
            "order" => 2,
            "visibility" => [
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
                    "translated" => true,
                ],
                "postcode" => [
                    "type" => "Text",
                    "label" => "Post Code",
                    "placeholder" => "Post Code",
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
                    "translated" => false,
                ],
            ]
        ];

        $document = [
            "name" => "documents",
            "title" => "Documents",
            "order" => 3,
            "visibility" => [
            ],
            "locations" => [
                'admin.profile.show',
                'admin.users.edit',
            ],
            "fields" => [
                "licence" => [
                    "type" => "File",
                    "label" => "Licence",
                    "placeholder" => "Your Licence",
                    "readonly" => false,
                    "disabled" => false,
                    "max_file_number" => 3,
                    "min_file_number" => 2,
                    "validation" => [
                        "rules" => [
                            "required",
                            "mimes:pdf,doc,docx",
                            "max:15",
                        ],
                        "messages" => []
                    ],
                    "translated" => false,
                ],
            ]
        ];

        FieldGroup::updateOrCreate(
            ['title' => $biodata['name']],
            ['data' => $biodata]
        );

        FieldGroup::updateOrCreate(
            ['title' => $address['name']],
            ['data' => $address]
        );

        FieldGroup::updateOrCreate(
            ['title' => $document['name']],
            ['data' => $document]
        );
    }
}
