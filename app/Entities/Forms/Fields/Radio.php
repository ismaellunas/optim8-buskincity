<?php

namespace App\Entities\Forms\Fields;

class Radio extends BaseField
{
    protected $type = 'Radio';

    public $options;

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->options = $data['options'] ?? [];
    }

    public function schema(): array
    {
        $schema = [
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
        $rules = $this->validation['rules'] ?? [];

        $this->adjustInRule($rules);

        $this->adjustNullableRule($rules);

        return $rules;
    }
}
