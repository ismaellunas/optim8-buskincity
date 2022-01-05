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
                            //"required",
                        ],
                        "messages" => []
                    ],
                    "wrapper" => [
                        "class" => [],
                        "style" => ""
                    ]
                ],
            ]
        ];

        Form::updateOrCreate(
            ['name' => $biodata['name']],
            ['data' => $biodata]
        );
    }
}
