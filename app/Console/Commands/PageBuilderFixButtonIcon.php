<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;

class PageBuilderFixButtonIcon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:pb-button-icon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update icon setting on button component.';

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
                        if ($entity['componentName'] == 'Button') {
                            $setting[$key]['config']['icon']['class'] = $setting[$key]['content']['button']['icon'];
                            $setting[$key]['config']['icon']['position'] = $setting[$key]['config']['button']['iconPosition'];

                            unset($setting[$key]['content']['button']['icon']);
                            unset($setting[$key]['config']['button']['iconPosition']);
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
