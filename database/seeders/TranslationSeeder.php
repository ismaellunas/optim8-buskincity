<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class TranslationSeeder extends Seeder
{
    public function run()
    {
        Artisan::call('fix:translation-source', [
            'mode' => 'forceCreate',
        ]);
    }
}
