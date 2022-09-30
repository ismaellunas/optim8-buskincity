<?php

namespace Database\Seeders;

use App\Models\GlobalOption;
use Illuminate\Database\Seeder;

class GlobalOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->populateDisciplineOptions();
    }

    private function populateDisciplineOptions()
    {
        $disciplines = [
            [
                "default_value" => "Acrobat",
                "name" => "Acrobat",
                "type" => "discipline",
            ],
            [
                "default_value" => "BalanceAct",
                "name" => "BalanceAct",
                "type" => "discipline",
            ],
            [
                "default_value" => "Clown",
                "name" => "Clown",
                "type" => "discipline",
            ],
            [
                "default_value" => "Acrobatic",
                "name" => "Acrobatic",
                "type" => "discipline",
            ],
            [
                "default_value" => "Dance/Break/Popping/Locking",
                "name" => "Dance/Break/Popping/Locking",
                "type" => "discipline",
            ],
            [
                "default_value" => "Escapologist",
                "name" => "Escapologist",
                "type" => "discipline",
            ],
            [
                "default_value" => "Juggler",
                "name" => "Juggler",
                "type" => "discipline",
            ],
            [
                "default_value" => "Magician",
                "name" => "Magician",
                "type" => "discipline",
            ],
            [
                "default_value" => "Multidisciplinary Circus/Variety",
                "name" => "Multidisciplinary Circus/Variety",
                "type" => "discipline",
            ],
            [
                "default_value" => "Musician/Singer/Band",
                "name" => "Musician/Singer/Band",
                "type" => "discipline",
            ],
            [
                "default_value" => "Music-Clown",
                "name" => "Music-Clown",
                "type" => "discipline",
            ],
            [
                "default_value" => "Music-Acrobat",
                "name" => "Music-Acrobat",
                "type" => "discipline",
            ],
            [
                "default_value" => "Performance",
                "name" => "Performance",
                "type" => "discipline",
            ],
            [
                "default_value" => "Pupeteer",
                "name" => "Pupeteer",
                "type" => "discipline",
            ],
            [
                "default_value" => "Stiltwalkers/Animation",
                "name" => "Stiltwalkers/Animation",
                "type" => "discipline",
            ],
            [
                "default_value" => "Visual Comedy",
                "name" => "Visual Comedy",
                "type" => "discipline",
            ],
            [
                "default_value" => "Other",
                "name" => "Other",
                "type" => "discipline",
            ],
        ];

        $this->createData($disciplines);
    }

    private function createData($options)
    {
        foreach ($options as $option) {
            GlobalOption::create($option);
        }
    }
}
