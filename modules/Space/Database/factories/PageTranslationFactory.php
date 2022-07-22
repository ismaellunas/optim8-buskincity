<?php
namespace Modules\Space\Database\factories;

use App\Helpers\Url;
use Modules\Space\Entities\Page;
use Modules\Space\Entities\PageTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Space\Entities\PageTranslation::class;

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
            'unique_key' => Url::randomDigitSegment([PageTranslation::class, 'isUniqueKeyExist']),
        ];
    }
}

