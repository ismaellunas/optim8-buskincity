<?php

namespace App\Console\Commands;

use App\Models\FieldGroup;
use Illuminate\Console\Command;

class FixCoverDimension extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:cover-dimension';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update cover dimension on custom fields.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fieldGroups = FieldGroup::all();

        foreach ($fieldGroups as $fieldGroup) {
            if ($fieldGroup['title'] == 'Media') {
                $fields = $fieldGroup->fields;

                foreach ($fields as $key => $field) {
                    if (
                        $field['type'] == 'FileDragDrop'
                        && $field['name'] == 'cover_background_photo'
                    ) {
                        $fields[$key]['notes'] = [
                            'Recommended dimension: ' . config('constants.recomended_dimensions.cover') . '.',
                        ];

                        $fields[$key]['media_dimension'] = [
                            "width" => config('constants.dimensions.cover.width'),
                            "height" => config('constants.dimensions.cover.height'),
                        ];
                    }
                }

                $fieldGroup->fields = $fields;
                $fieldGroup->save();
            }
        }

        return Command::SUCCESS;
    }
}
