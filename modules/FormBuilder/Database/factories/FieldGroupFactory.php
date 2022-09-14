<?php

namespace Modules\FormBuilder\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FieldGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\FormBuilder\Entities\FieldGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->words(2, true);

        return [
            'name' => Str::title($title),
            'title' => Str::snake($title), // form id
            'type' => $this->model::TYPE,
            'data' => [
                'name' => null,
                'title' => null,
                'order' => null,
                'visibility' => [],
                'location' => [],
                'fields' => [
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-half",
                        "label" => "First Name",
                        "name" => "first_name",
                        "placeholder" => null,
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "max" => null,
                                "min" => null,
                                "regex" => null
                            ],
                            "message" => []
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDL7JTZGED99",
                        "properties" => [],
                        "data" => [],
                        "attributes" => []
                    ],
                    [
                        "type" => "Text",
                        "title" => "Text",
                        "column" => "is-half",
                        "label" => "Last Name",
                        "name" => "last_name",
                        "placeholder" => null,
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "max" => null,
                                "min" => null,
                                "regex" => null
                            ],
                            "message" => []
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDL7JU0FG06G",
                        "properties" => [],
                        "data" => [],
                        "attributes" => []
                    ],
                    [
                        "type" => "Email",
                        "title" => "Email",
                        "column" => "is-full",
                        "label" => "Email",
                        "name" => "email",
                        "placeholder" => "e.g. example@mail.com",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => true,
                                "max" => null,
                                "min" => null,
                                "email" => true
                            ],
                            "message" => []
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDL7JU1ADQ6F",
                        "properties" => [],
                        "data" => [],
                        "attributes" => []
                    ],
                    [
                        "type" => "Select",
                        "title" => "Select",
                        "column" => "is-full",
                        "label" => "Gender",
                        "name" => "gender",
                        "placeholder" => null,
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "options" => [
                            [
                                "id" => "m",
                                "value" => "Male"
                            ],
                            [
                                "id" => "f",
                                "value" => "Female"
                            ]
                        ],
                        "validation" => [
                            "rules" => [
                                "required" => false
                            ],
                            "message" => []
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDL7JU2P4NYV",
                        "properties" => [],
                        "data" => [],
                        "attributes" => []
                    ],
                    [
                        "type" => "Number",
                        "title" => "Number",
                        "column" => "is-half",
                        "label" => "Age",
                        "name" => "age",
                        "placeholder" => "e.g. 25",
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => false,
                                "min" => null,
                                "max" => null
                            ],
                            "message" => []
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDL7JU20HA42",
                        "properties" => [],
                        "data" => [],
                        "attributes" => []
                    ],
                    [
                        "title" => "Textarea",
                        "componentName" => "Textarea",
                        "config" => [
                            "properties" => [
                                "name" => null,
                                "label" => "Textarea",
                                "placeholder" => null
                            ],
                            "data" => [
                                "default" => null
                            ],
                            "validation" => [
                                "required" => false,
                                "minLength" => null,
                                "maxLength" => null
                            ],
                            "attributes" => [
                                "disabled" => false,
                                "readonly" => false
                            ]
                        ],
                        "type" => "Textarea",
                        "column" => "is-full",
                        "label" => "Message",
                        "name" => "message",
                        "placeholder" => null,
                        "note" => null,
                        "default_value" => null,
                        "readonly" => false,
                        "disabled" => false,
                        "validation" => [
                            "rules" => [
                                "required" => false,
                                "max" => null,
                                "min" => null
                            ],
                            "message" => []
                        ],
                        "visibility" => [],
                        "translated" => false,
                        "id" => "IDL7JU41LBFE",
                        "properties" => [],
                        "data" => [],
                        "attributes" => []
                    ]
                ],
            ],
        ];
    }
}

