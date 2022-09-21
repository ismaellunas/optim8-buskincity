<?php

namespace Modules\FormBuilder\Forms;

use App\Entities\Forms\Form as AppForm;

class Form extends AppForm
{
    protected function buttons(): array
    {
        $submitText = $this->data['settings']
            ->where('key', 'submit_text')
            ->value('value')
            ?? 'Submit';

        return [
            'submit' => [
                'label' => $submitText
            ]
        ];
    }
}
