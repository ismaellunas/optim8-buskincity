<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PageTranslation::class;

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
            'status' => PageTranslation::STATUS_DRAFT,
            'page_id' => Page::factory(),
            'data' => json_decode('{"structures": [], "entities": {}, "media": []}'),
            'plain_text_content' => null,
        ];
    }
}
