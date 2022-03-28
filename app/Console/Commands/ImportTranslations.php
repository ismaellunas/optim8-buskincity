<?php

namespace App\Console\Commands;

use Database\Seeders\TranslationSeeder;
use Illuminate\Console\Command;

class ImportTranslations extends Command
{
    protected $signature = 'import:translation {--replace}';
    protected $description = 'Import all translation to database.';

    public function handle()
    {
        $translation = app(TranslationSeeder::class);
        $translation->run($this->option('replace'));

        $this->info('The import translation is successful!');
        return Command::SUCCESS;
    }
}
