<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;

class FixPageImageSizeAndPosition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:page-image-size-and-position';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix image config structure on image and card components.';

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
                            || $entity['componentName'] == 'Image'
                        ) {
                            $data[$id]['config']['image']['width'] = null;
                            $data[$id]['config']['image']['height'] = null;
                            $data[$id]['config']['image']['position'] = null;
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
