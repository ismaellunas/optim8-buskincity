<?php

namespace App\Entities\Forms\Fields;

use Illuminate\Support\Str;

class Select
{
    protected $data;
    protected $type = "Select";

    public $name;
    public $value;

    public $defaultValue;
    public $disabled;
    public $label;
    public $options;
    public $placeholder;
    public $readonly;
    public $validation;

    public function __construct(string $name, array $data = [])
    {
        $this->data = $data;
        $this->name = $name;
        $this->value = $data['value'] ?? '';
        $this->validation = $data['validation'];
        $this->label = $data['label'] ?? null;
        $this->placeholder = $data['placeholder'] ?? '- Select -';
        $this->defaultValue = $data['default_value'] ?? null;
        $this->disabled = $data['disabled'] ?? false;
        $this->readonly = $data['readonly'] ?? false;
        $this->options = $data['options'] ?? [];
    }

    public function schema(): array
    {
        $schema = [
            'type' => $this->type,
            'name' => $this->name,
            'label' => $this->label,
            'default_value' => $this->defaultValue,
            'is_disabled' => $this->disabled,
            'is_readonly' => $this->readonly,
            'is_required' => $this->isRequired(),
            'placeholder' => $this->placeholder,
            'options' => $this->options,
            'wrapper' => [
                'class' => [],
            ],
        ];

        return $schema;
    }

    protected function isRequired(): bool
    {
        if (!empty($this->validation['rules'])) {
            return in_array('required', $this->validation['rules']);
        }
        return false;
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

    public function formattedRules(): array
    {
        $rules = [];

        foreach($this->validationRules() as $rule) {

            if (is_string($rule)) {
                if (Str::contains($rule, ":")) {
                    list($ruleName, $ruleParams) = explode(":", $rule);
                    $rules[$ruleName] = explode(',', $ruleParams);
                } else {
                    $rules[] = $rule;
                }
            }
        }
        return $rules;
    }

    public function validationMessages(): array
    {
        return $this->validation['messages'] ?? [];
    }
}
