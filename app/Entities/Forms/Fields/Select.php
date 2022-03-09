<?php

namespace App\Entities\Forms\Fields;

use App\Models\User;

class Select extends BaseField
{
    protected $type = "Select";

    public $options;
    public $placeholder;
    public $defaultValue = '';

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->placeholder = $data['placeholder'] ?? '- Select -';
        $this->options = $data['options'] ?? [];
    }

    public function schema(): array
    {
        $schema = [
            'placeholder' => $this->placeholder,
            'options' => $this->options,
        ];

        return array_merge(
            parent::schema(),
            $schema
        );
    }

    private function adjustInRule(&$rules)
    {
        $rules[$this->name][] = 'in:'.implode(',', array_keys($this->options));
    }

    public function validationRules(User $entity = null): array
    {
        $rules = parent::validationRules($entity);

        $this->adjustInRule($rules);

        return $rules;
    }
}
