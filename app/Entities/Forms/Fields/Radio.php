<?php

namespace App\Entities\Forms\Fields;

use Illuminate\Support\Str;

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
            'type' => $this->type,
            'name' => $this->name,
            'label' => $this->label,
            'default_value' => $this->defaultValue,
            'is_disabled' => $this->disabled,
            'is_required' => $this->isRequired(),
            'options' => $this->options,
        ];

        return $schema;
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
