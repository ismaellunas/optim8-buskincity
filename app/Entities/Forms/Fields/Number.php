<?php

namespace App\Entities\Forms\Fields;

class Number extends Text
{
    protected $type = "Number";

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

        $rules[] = "numeric";

        return $rules;
    }
}
