<?php

namespace Modules\FormBuilder\Forms;

use App\Entities\Forms\Form as AppForm;
use Modules\FormBuilder\ModuleService;

class Form extends AppForm
{
    protected function buttons(): array
    {
        $submitText = $this->data['settings']
            ->where('key', 'submit_text')
            ->value('value')
            ?? ModuleService::defaultSettings()['submit_text'];

        return [
            'submit' => [
                'label' => $submitText
            ]
        ];
    }
}
