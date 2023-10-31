<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ImportTranslations extends Command
{
    protected $signature = 'import:translation {--replace}';
    protected $description = 'Import all translation to database.';

    public function handle()
    {
        $arguments = [];

        if ($this->option('replace')) {
            $arguments['mode'] = 'replace';
        }

        Artisan::call('fix:translation-source', $arguments);

        $this->info('The import translation is successfully!');
        return Command::SUCCESS;
    }
}
