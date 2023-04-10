<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
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
            'extension' => collect(config('constants.extensions.image'))->random(),
            'file_type' => 'image',
            'file_url' => $this->faker->imageUrl(360, 360, null, true, null, true),
            'size' => $this->faker->numberBetween(512),
            'type' => Media::TYPE_DEFAULT,
        ];
    }

    public function author(int|User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => is_int($user) ? $user : $user->id,
            ];
        });
    }
}
