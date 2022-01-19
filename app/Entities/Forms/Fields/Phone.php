<?php

namespace App\Entities\Forms\Fields;

class Phone extends Text
{
    protected $type = "Phone";

    public function schema(): array
    {
        $schema = [
            'maxlength' => $this->maxlength ?? $this->max() ?? null,
            'placeholder' => $this->placeholder,
        ];

        return array_merge(parent::schema(), $schema);
    }

    public function validationRules(): array
    {
        $rules = parent::validationRules();

        if (!$this->isRequired()) {
            $rules[] = 'nullable';
        }
        $rules[] = "regex:/^(?:\+?(\d{1,3}))?([\d]+)$/";
        $rules[] = "min:10";

        return $rules;
    }
}
