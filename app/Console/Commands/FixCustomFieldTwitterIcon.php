<?php

namespace App\Console\Commands;

use App\Models\FieldGroup;
use Illuminate\Console\Command;

class FixCustomFieldTwitterIcon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:custom-field-twitter-icon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update twitter icon on custom field to x-twitter icon.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fieldGroups = FieldGroup::all();

        foreach ($fieldGroups as $fieldGroup) {
            if ($fieldGroup['title'] == 'Social media') {
                $fields = $fieldGroup->fields;

                foreach ($fields as $key => $field) {
                    if (
                        $field['type'] == 'Text'
                        && $field['name'] == 'twitter'
                    ) {
                        $fields[$key]['left_icon'] = "fa-brands fa-x-twitter";
                    }
                }

                $fieldGroup->fields = $fields;
                $fieldGroup->save();
            }
        }

        return Command::SUCCESS;
    }
}
