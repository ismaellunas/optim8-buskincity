<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;

class FixPageTranslationFormBuilderComponentStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:page-translation-form-builder-component-structure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Fix structure object of FormBuilder components in PageTranslation's data";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        PageTranslation::chunk(5, function ($pageTranslations) {

            foreach ($pageTranslations as $pageTranslation) {
                $isChanged = false;

                $structures = $pageTranslation->data['structures'];

                if (! $structures) continue;

                foreach ($structures as &$structure) {
                    foreach ($structure['columns'] as &$column) {
                        foreach ($column['components'] as &$component) {
                            if ($component['componentName'] == 'FormBuilder') {
                                $isChanged = true;
                                $component['module'] = 'FormBuilder';
                            }
                        }
                    }
                }

                if ($isChanged) {
                    $pageTranslation->data['structures'] = $structures;
                    $pageTranslation->save();
                }
            }
        });

        return Command::SUCCESS;
    }
}
