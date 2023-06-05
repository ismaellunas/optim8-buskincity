<?php

namespace App\Console\Commands;

use App\Models\FieldGroup;
use Illuminate\Console\Command;

class FixFormBuilderNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:fb-notes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update notes on form builder and custom fields';

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
                    && $field['name'] == 'cover_background_photo'
                ) {
                    $fields[$key]['notes'] = [
                        'Recommended dimension: ' . config('constants.recomended_dimensions.cover') . '.',
                    ];
                } else {
                    $note = $field['note'];

                    if (
                        $note
                        && ! empty($note)
                    ) {
                        $fields[$key]['notes'] = [
                            $note
                        ];
                    } else {
                        $fields[$key]['notes'] = [];
                    }
                }

                unset($fields[$key]['note']);
            }

            $fieldGroup->fields = $fields;
            $fieldGroup->save();
        }

        return Command::SUCCESS;
    }
}
