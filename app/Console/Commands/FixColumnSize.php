<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;

class FixColumnSize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:column-size';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix structure on column component to add column size.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pageTranslations = PageTranslation::all();

        foreach ($pageTranslations as $pageTranslation) {
            $structures = collect($pageTranslation['data']['structures'])
                ->map(function ($structure) {
                    return [
                        "id" => $structure['id'],
                        "totalColumn" => count($structure['columns'])
                    ];
                })
                ->keyBy('id')
                ->all();

            $pageTranslation['data']->transform(function ($data, $key) use ($structures) {
                if ($key == "entities") {
                    foreach ($data as $id => $entity) {
                        if ($entity['componentName'] == 'Columns') {
                            $columns = [];

                            if (isset($structures[$entity['id']])) {
                                for ($i = 0; $i < $structures[$entity['id']]['totalColumn']; $i++) {
                                    $columns[] = [
                                        'size' => "auto",
                                    ];
                                }

                                $data[$id]['config']['columns'] = $columns;
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
