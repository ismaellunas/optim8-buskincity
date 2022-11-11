<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;

class FixColumnCustomId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:column-custom-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix structure on column component to add custom id.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pageTranslations = PageTranslation::all();

        foreach ($pageTranslations as $pageTranslation) {
            $pageTranslation['data']->transform(function ($data, $key) {
                if ($key == "entities") {
                    foreach ($data as $id => $entity) {
                        if ($entity['componentName'] == 'Columns') {
                            $data[$id]['config']['wrapper']['customId'] = null;
                        }
                    }
                }

                return $data;
            });

            $pageTranslation->save();
        }

        return Command::SUCCESS;
    }
}
