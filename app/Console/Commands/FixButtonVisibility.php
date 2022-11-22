<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;

class FixButtonVisibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:button-visibility';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix structure on button component to add visibility device.';

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
                            $data[$id]['config']['visibility']['device'] = null;
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
