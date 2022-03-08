<?php

namespace App\Entities\Forms\Fields;

use App\Models\User;

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

    public function validationRules(User $entity = null): array
    {
        $rules = parent::validationRules($entity);

        $rules[$this->name] = "numeric";

        return $rules;
    }
}
