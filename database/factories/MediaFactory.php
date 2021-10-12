<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'file_name' => $this->faker->unixTime(),
            'extension' => collect(Media::$imageExtensions)->random(),
            'file_type' => 'image',
            'file_url' => $this->faker->imageUrl(360, 360, null, true, null, true),
            'size' => $this->faker->numberBetween(512),
        ];
    }
}
