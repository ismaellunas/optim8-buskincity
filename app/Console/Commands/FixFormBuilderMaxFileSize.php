<?php

namespace App\Console\Commands;

use App\Models\FieldGroup;
use App\Services\MediaService;
use Illuminate\Console\Command;

class FixFormBuilderMaxFileSize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:fb-max-file-size';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update max file size on form builder.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fieldGroups = FieldGroup::all();
        $defaultMaxFileSize = MediaService::maxFileSize();

        foreach ($fieldGroups as $fieldGroup) {
            $fields = $fieldGroup->fields;

            foreach ($fields as $key => $field) {
                if (
                    $field['type'] == 'FileDragDrop'
                ) {
                    $max = (int)$field['validation']['rules']['max'];

                    if ($max > $defaultMaxFileSize) {
                        $fields[$key]['validation']['rules']['max'] = $defaultMaxFileSize;
                    }
                }
            }

            $fieldGroup->fields = $fields;
            $fieldGroup->save();
        }

        return Command::SUCCESS;
    }
}
