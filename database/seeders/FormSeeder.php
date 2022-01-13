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
                ],
                "phone" => [
                    "type" => "Phone",
                    "label" => "Phone",
                    "placeholder" => "Phone",
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "validation" => [
                        "rules" => [
                        ],
                        "messages" => [],
                    ],
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
                ],
                "next_of_kin" => [
                    "type" => "Text",
                    "label" => "Next Of Kin",
                    "placeholder" => "Next Of Kin",
                    "default_value" => "",
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
                ],

                "criminal_record" => [
                    "type" => "Textarea",
                    "label" => "Criminal Record",
                    "placeholder" => "...",
                    "default_value" => null,
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
                    ]
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
                    "validation" => [
                        "rules" => [
                            "required",
                        ],
                        "messages" => [],
                    ],
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
                    "default_value" => null,
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => "",
                    "rows" => "",
                    "validation" => [
                        "rules" => [
                            "required",
                            "max:20"
                        ],
                        "messages" => []
                    ],
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
    }
}
