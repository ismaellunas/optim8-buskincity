<?php

namespace Database\Factories;

use App\Helpers\Url;
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
        $isCodeExist = function ($code) {
            return PageTranslation::where('unique_key', $code)->exists();
        };

        return [
            'locale' => config('app.fallback_locale'),
            'title' => $title,
            'slug' => Str::slug($title),
            'status' => PageTranslation::STATUS_DRAFT,
            'page_id' => Page::factory(),
            'data' => json_decode('{"structures": [], "entities": {}, "media": []}'),
            'unique_key' => Url::randomDigitSegment($isCodeExist),
        ];
    }
}
