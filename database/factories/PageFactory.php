<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->sentence(5);
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'status' => Page::STATUS_DRAFT,
            'meta_title' => $title,
            'meta_description' => $this->faker->text(100),
            'data' => [
                [
                    "id" => "IDKR5S4LSMCA",
                    "type" => "columns",
                    "columns" => [
                        [
                            "id" => "IDKR5S4IW2KG",
                            "components" => [
                                [
                                    "title" => "H1",
                                    "componentName" => "H1",
                                    "content" => [
                                        "h1" => [
                                            "html" => "<p>$title</p>",
                                            "attrs" => [
                                                "class" => [],
                                            ],
                                        ],
                                    ],
                                    "id" => "IDKR5S78CDIA",
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    "id" => "IDKR5S7GLUBO",
                    "type" => "columns",
                    "columns" => [
                        [
                            "id" => "IDKR5S4IW309",
                            "components" => [
                                [
                                    "title" => "Card Text",
                                    "componentName" => "ContentCardText",
                                    "content" => [
                                        "cardContent" => [
                                            "content" => [
                                                "html" => "<p><strong>What is Lorem Ipsum?</strong><br>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>",
                                                "attributes" => [
                                                    "class" => [],
                                                ],
                                            ],
                                            "media" => [],
                                        ],
                                    ],
                                    "id" => "IDKR5S7M6RCY",
                                ],
                            ],
                        ],
                        [
                            "id" => "IDKR5S4IW3T1",
                            "components" => [
                                [
                                    "title" => "Card Text",
                                    "componentName" => "ContentCardText",
                                    "content" => [
                                        "cardContent" => [
                                            "content" => [
                                                "html" => "<p><strong>Where can I get some?</strong><br>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet.</p>",
                                                "attributes" => [
                                                    "class" => [],
                                                ],
                                            ],
                                            "media" => [],
                                        ],
                                    ],
                                    "id" => "IDKR5S7OGEMA",
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];
    }
}
