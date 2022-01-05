<?php

namespace Database\Seeders;

use App\Models\Form;
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
            "title" => "Biodata",
            "visibility" => [
                "roles" => [],
            ],
            "fields" => [
                "postcode" => [
                    "type" => "Text",
                    "label" => "Post Code",
                    "placeholder" => "Post Code",
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => 6,
                    "validation" => [
                        "rules" => [
                            "required",
                            "max:6"
                        ],
                        "messages" => []
                    ],
                    "wrapper" => [
                        "class" => [],
                        "style" => ""
                    ]
                ],
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
                        "roles" => ['Human Resource'],
                    ],
                ],
                "gender" => [
                    "type" => "Radio",
                    "label" => "Gender",
                    "disabled" => false,
                    "default_value" => "male",
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
                "skills" => [
                    "type" => "CheckboxGroup",
                    "label" => "Skills",
                    "disabled" => false,
                    "readonly" => false,
                    "default_value" => ['php'],
                    "is_raw" => false,
                    "layout" => "vertical",
                    "options" => [
                        "php" => "PHP",
                        "js" => "Javascript",
                        "pgsql" => "PostgreSQL",
                    ],
                    "validation" => [
                        "rules" => [],
                        "messages" => [],
                    ],
                ],
                "term_and_condition" => [
                    "type" => "Checkbox",
                    "label" => "Term and Condition",
                    "text" => "I agree to the <a href='/'>terms and conditions</a>",
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

        Form::updateOrCreate(
            ['name' => $biodata['name']],
            ['data' => $biodata]
        );
    }
}
