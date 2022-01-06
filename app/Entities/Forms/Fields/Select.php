<?php

namespace App\Entities\Forms\Fields;

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
        $rules[] = 'in:'.implode(',', array_keys($this->options));
    }

    private function adjustNullableRule(&$rules)
    {
        if (!$this->isRequired()) {
            $rules[] = 'nullable';
        }
    }

    public function validationRules(): array
    {
        $rules = parent::validationRules();

        $this->adjustInRule($rules);

        $this->adjustNullableRule($rules);

        return $rules;
    }
}
