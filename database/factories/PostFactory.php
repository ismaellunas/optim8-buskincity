<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->sentence();

        return [
            'locale' => config('app.fallback_locale'),
            'title' => $title,
            'slug' => Str::slug($title),
            'status' => Post::STATUS_DRAFT
        ];
    }

    public function fakeContent()
    {
        return $this->state(function (array $attributes) {
            $content = "";
            for ($i = 0; $i < 5; $i++) {
                $content .= "<p>".$this->faker->paragraph(10)."</p>";
            }

            return [
                'content' => $content,
                'plain_text_content' => strip_tags($content),
            ];
        });
    }

    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Post::STATUS_PUBLISHED,
            ];
        });
    }
}
