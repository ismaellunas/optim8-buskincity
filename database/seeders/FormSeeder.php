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
            "fields" => [
                "postcode" => [
                    "type" => "Text",
                    "label" => "Post Code",
                    "placeholder" => "Post Code",
                    "default_value" => "",
                    "readonly" => false,
                    "disabled" => false,
                    "maxlength" => 3,
                    "validation" => [
                        "rules" => [
                            "required",
                            "max:1"
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
                    "wrapper" => [
                        "class" => [],
                        "style" => ""
                    ]
                ],
                "job" => [
                    "type" => "Select",
                    "label" => "Job",
                    //"placeholder" => "Current Job/Position",
                    "readonly" => false,
                    "disabled" => false,
                    "default_value" => "programmer",
                    "options" => [
                        "programmer" => "Programmer",
                        "pro-gamer" => "Pro-gamer",
                    ],
                    "validation" => [
                        "rules" => [
                            "required",
                        ],
                        "messages" => []
                    ],
                    "wrapper" => [
                        "class" => [],
                        "style" => ""
                    ]
                ],

                "gender" => [
                    "type" => "Radio",
                    "label" => "Gender",
                    "disabled" => false,
                    //"default_value" => "male",
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

                "term_and_condition" => [
                    "type" => "Checkbox",
                    "label" => "Term and Condition",
                    "text" => "I agree to the <a href='/'>terms and conditions</a>",
                    "disabled" => false,
                    "true_value" => true,
                    "false_value" => false,
                    "default_value" => false,
                    "is_raw" => false,
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
