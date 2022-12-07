<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;

class FixPageCardShadow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:page-card-shadow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add is shadowless config on card and card text component';

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

                        if (
                            $entity['componentName'] == 'Card'
                            || $entity['componentName'] == 'CardText'
                        ) {
                            $data[$id]['config']['card']['isShadowless'] = false;
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
