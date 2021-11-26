<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \Barryvdh\TranslationManager\Manager;

class TranslationSeeder extends Seeder
{
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Set true if data will be replace existing translations
        // Set false if data will be append new translations
        $this->manager->importTranslations(true);
    }
}
