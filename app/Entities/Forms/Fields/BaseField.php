<?php

namespace App\Entities\Forms\Fields;

use Illuminate\Support\Str;

abstract class BaseField
{
    protected $data;
    protected $type;
    protected $emptyValue = null;

    public $defaultValue;
    public $disabled;
    public $label;
    public $readonly;
    public $validation;
    public $value;

    public function __construct(string $name, array $data = [])
    {
        $this->data = $data;
        $this->name = $name;

        $this->disabled = $data['disabled'] ?? false;
        $this->label = $data['label'] ?? null;
        $this->readonly = $data['readonly'] ?? false;
        $this->validation = $data['validation'];

        if (array_key_exists('default_value', $data)) {
            $this->defaultValue = $data['default_value'];
        }

        $this->value = $data['value'] ?? $this->defaultValue;
    }

    protected function isRequired(): bool
    {
        if (!empty($this->validation['rules'])) {
            return in_array('required', $this->validation['rules']);
        }

        return false;
    }

    protected function schema(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'label' => $this->label,
            'is_disabled' => $this->disabled,
            'is_required' => $this->isRequired(),
            'default_value' => $this->defaultValue,
            'value' => $this->value,
        ];
    }

    public function validationRules(): array
    {
        $rules = $this->validation['rules'] ?? [];

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
