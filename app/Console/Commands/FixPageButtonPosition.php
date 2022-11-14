<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FixPageButtonPosition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:page-button-position';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix button position on page (2022-11-XX)';

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

                            $position = $data[$id]['config']['button']['position'];

                            if (Str::contains($position, 'is-pulled')) {
                                $data[$id]['config']['button']['position'] = Str::replace('is-pulled', 'has-text', $position);
                            }
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
