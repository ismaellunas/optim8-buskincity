<?php

namespace App\Console\Commands;

use App\Models\Form;
use Illuminate\Console\Command;
use Modules\FormBuilder\Entities\Form as FormModule;

class FixFormButtonPosition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:form-button-position';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add button position config';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $forms = [
            ...Form::all(),
            ...FormModule::all(),
        ];

        foreach ($forms as $form) {
            $setting = $form->setting;

            $setting['button']['position'] = null;

            $form->setting = $setting;
            $form->save();
        }

        return Command::SUCCESS;
    }
}
