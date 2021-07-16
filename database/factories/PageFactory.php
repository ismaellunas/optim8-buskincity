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
                    "id" => "IDKR5WMPKPIS",
                    "type" => "columns",
                    "columns" => [
                        [
                            "id" => "IDKR5WMDUNXD",
                            "components" => [
                                [
                                    "title" => "Heading",
                                    "componentName" => "Heading",
                                    "content" => [
                                        "heading" => [
                                            "html" => "<p>Lorem Ipsum</p>",
                                            "tag" => "h1",
                                            "attrs" => [
                                                "class" => [
                                                    "title",
                                                    "is-1",
                                                ],
                                            ],
                                        ],
                                    ],
                                    "id" => "IDKR5WUCTHBA",
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    "id" => "IDKR5WN9XABD",
                    "type" => "columns",
                    "columns" => [
                        [
                            "id" => "IDKR5WMDUNK8",
                            "components" => [
                                [
                                    "title" => "Card Text",
                                    "componentName" => "ContentCardText",
                                    "content" => [
                                        "cardContent" => [
                                            "content" => [
                                                "html" => "<p><strong>What is Lorem Ipsum?</strong></p><p><br>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>",
                                                "attributes" => [
                                                    "class" => [],
                                                ],
                                            ],
                                            "media" => [],
                                        ],
                                    ],
                                    "id" => "IDKR5WNG473V",
                                ],
                            ],
                        ],
                        [
                            "id" => "IDKR5WMDUNQ0",
                            "components" => [
                                [
                                    "title" => "Card Text",
                                    "componentName" => "ContentCardText",
                                    "content" => [
                                        "cardContent" => [
                                            "content" => [
                                                "html" => "<p><strong>Why do we use it?</strong></p><p><br>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English.</p>",
                                                "attributes" => [
                                                    "class" => [],
                                                ],
                                            ],
                                            "media" => [],
                                        ],
                                    ],
                                    "id" => "IDKR5WNIMWTR",
                                ],
                            ],
                        ],
                        [
                            "id" => "IDKR5WMDUNAE",
                            "components" => [
                                [
                                    "title" => "Card Text",
                                    "componentName" => "ContentCardText",
                                    "content" => [
                                        "cardContent" => [
                                            "content" => [
                                                "html" => "<p><strong>Where does it come from?</strong></p><p><br>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source.</p>",
                                                "attributes" => [
                                                    "class" => [],
                                                ],
                                            ],
                                            "media" => [],
                                        ],
                                    ],
                                    "id" => "IDKR5WNK2MVN",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
