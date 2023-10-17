<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;

class FixPageBuilderRemoveMultipleAlignment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:pb-remove-multiple-alignment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove multiple alignment on Page Builder Component has rich text editor';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pageTranslations = PageTranslation::all();

        foreach ($pageTranslations as $pageTranslation) {
            $pageTranslation->data->transform(function ($setting, $key) {
                if ($key == 'entities') {
                    foreach ($setting as $key => $entity) {
                        if ($entity['componentName'] == 'Text') {
                            unset($setting[$key]['config']['text']['alignment']);
                        }

                        if ($entity['componentName'] == 'Card') {
                            unset($setting[$key]['config']['content']['alignment']);
                        }
                    }
                }

                return $setting;
            });

            $pageTranslation->save();
        }

        return Command::SUCCESS;
    }
}
