<?php

namespace App\Console\Commands;

use App\Models\FieldGroup;
use Illuminate\Console\Command;

class FixFormBuilderFileUploadEditorToggle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:fb-fileupload-editor-toggle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add image editor toggle setting on file upload Form Builder.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fieldGroups = FieldGroup::all();

        foreach ($fieldGroups as $fieldGroup) {
            $fields = $fieldGroup->fields;

            foreach ($fields as $key => $field) {
                if (
                    $field['type'] == 'FileDragDrop'
                ) {
                    $fields[$key]['is_image_editor_enabled'] = true;
                }
            }

            $fieldGroup->fields = $fields;
            $fieldGroup->save();
        }

        return Command::SUCCESS;
    }
}
