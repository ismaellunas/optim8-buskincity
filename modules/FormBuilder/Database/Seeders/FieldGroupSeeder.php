<?php

namespace Modules\FormBuilder\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\FormBuilder\Entities\FieldGroup;
use Faker\Factory as Faker;

class FieldGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $faker = Faker::create();

        FieldGroup::factory()
            ->hasEntries(10, function (array $attributes, FieldGroup $fieldGroup) use ($faker) {
                return [
                    'first_name' => $faker->firstName(),
                    'last_name' => $faker->lastName(),
                    'email' => $faker->email(),
                    'gender' => $faker->randomElement(['f', 'm']),
                    'age' => $faker->numberBetween(10, 25),
                    'message' => $faker->paragraph(),
                ];
            })
            ->count(10)
            ->create();
    }
}
