<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FixPageButtonTextWeight extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:page-button-text-weight';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add text weight setting on button component';

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

                        if ($entity['componentName'] == 'Button') {

                            $data[$id]['config']['button']['textWeight'] = null;
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
