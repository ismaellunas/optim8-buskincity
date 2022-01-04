<?php

namespace App\Entities\Forms\Fields;

use Illuminate\Support\Str;

class Text
{
    protected $data;
    protected $type = "Text";

    public $name;
    public $value;

    public $defaultValue;
    public $disabled;
    public $label;
    public $maxlength;
    public $placeholder;
    public $readonly;
    public $validation;

    public function __construct(string $name, array $data = [])
    {
        $this->data = $data;
        $this->name = $name;
        $this->value = $data['value'] ?? null;

        $this->defaultValue = $data['default_value'] ?? null;
        $this->disabled = $data['disabled'] ?? false;
        $this->label = $data['label'] ?? null;
        $this->maxlength = $data['maxlength'] ?? null;
        $this->placeholder = $data['placeholder'] ?? null;
        $this->readonly = $data['readonly'] ?? false;
        $this->validation = $data['validation'];
    }

    public function schema(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'label' => $this->label,
            'default_value' => $this->defaultValue,
            'is_disabled' => $this->disabled,
            'is_readonly' => $this->readonly,
            'is_required' => $this->isRequired(),
            'maxlength' => $this->maxlength ?? $this->max() ?? null,
            'placeholder' => $this->placeholder,
            'wrapper' => [
                'class' => [],
            ],
        ];
    }

    protected function isRequired(): bool
    {
        if (!empty($this->validation['rules'])) {
            return in_array('required', $this->validation['rules']);
        }
        return false;
    }

    public function validationRules(): array
    {
        return $this->validation['rules'] ?? [];
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

    protected function max(): ?int
    {
        $rules = $this->formattedRules();

        if (!empty($rules['max'])) {
            return (int) $rules['max'][0];
        }

        return null;
    }
}
