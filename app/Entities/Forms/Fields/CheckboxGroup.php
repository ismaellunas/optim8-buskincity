<?php

namespace App\Entities\Forms\Fields;

use App\Contracts\ArrayValueFieldInterface;

class CheckboxGroup extends BaseField implements ArrayValueFieldInterface
{
    protected $type = "CheckboxGroup";

    public $defaultValue = [];
    public $options;
    public $isRaw;
    public $layout;

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->isRaw = $data['is_raw'] ?? false;
        $this->options = $data['options'] ?? [];
        $this->layout = $data['layout'] ?? 'vertical';
    }

    public function schema(): array
    {
        $schema = [
            'options' => $this->options,
            'is_raw' => $this->isRaw,
            'layout' => $this->layout,
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
        $rules = array_merge(
            parent::validationRules(),
            ['array']
        );

        $this->adjustNullableRule($rules);

        return $rules;
    }

    public function arrayValidationRules(): array
    {
        $rules = [];

        $this->adjustInRule($rules);

        return $rules;
    }
}
