<?php

namespace App\Console\Commands;

use App\Models\PageTranslation;
use Illuminate\Console\Command;

class FixPagecolumnConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:page-column-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix column config structure on column component.';

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
                                $data[$id]['config']['columns'] = [];

                                for ($i = 0; $i < $structures[$entity['id']]['totalColumn']; $i++) {
                                    $columns[] = [
                                        'size' => "auto",
                                    ];
                                }

                                $data[$id]['config']['columns']['column'] = $columns;
                                $data[$id]['config']['columns']['isCentered'] = false;
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
