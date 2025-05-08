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
        $this->populateSpaceAndDisciplineOptions();
    }

    private function populateSpaceAndDisciplineOptions()
    {
        $spaceAndDisciplinesData = [
            ["name" => "Country", "type" => "space", "default_value" => null],
            ["name" => "Pitch", "type" => "space", "default_value" => null],
            ["name" => "City", "type" => "space", "default_value" => null],
            ["name" => "Acrobat", "type" => "discipline", "default_value" => "Acrobat"],
            ["name" => "BalanceAct", "type" => "discipline", "default_value" => "BalanceAct"],
            ["name" => "Clown", "type" => "discipline", "default_value" => "Clown"],
            ["name" => "Acrobatic", "type" => "discipline", "default_value" => "Acrobatic"],
            ["name" => "Escapologist", "type" => "discipline", "default_value" => "Escapologist"],
            ["name" => "Juggler", "type" => "discipline", "default_value" => "Juggler"],
            ["name" => "Magician", "type" => "discipline", "default_value" => "Magician"],
            ["name" => "Music-Clown", "type" => "discipline", "default_value" => "Music-Clown"],
            ["name" => "Music-Acrobat", "type" => "discipline", "default_value" => "Music-Acrobat"],
            ["name" => "Performance", "type" => "discipline", "default_value" => "Performance"],
            ["name" => "Pupeteer", "type" => "discipline", "default_value" => "Pupeteer"],
            ["name" => "Visual Comedy", "type" => "discipline", "default_value" => "Visual Comedy"],
            ["name" => "Other", "type" => "discipline", "default_value" => "Other"],
            ["name" => "Dance", "type" => "discipline", "default_value" => "Dance"],
            ["name" => "Multidisciplinary Circus", "type" => "discipline", "default_value" => "Multidisciplinary Circus"],
            ["name" => "Musician", "type" => "discipline", "default_value" => "Musician"],
            ["name" => "Stiltwalkers", "type" => "discipline", "default_value" => "Stiltwalkers"],
            ["name" => "Special Events / Festivals", "type" => "space", "default_value" => null],
        ];

        $this->createData($spaceAndDisciplinesData);
    }

    private function createData($options)
    {
        foreach ($options as $option) {
            GlobalOption::create($option);
        }
    }
}
