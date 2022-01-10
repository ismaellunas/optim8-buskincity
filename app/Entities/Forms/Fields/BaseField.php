<?php

namespace App\Entities\Forms\Fields;

use App\Models\User;
use Illuminate\Support\Str;

abstract class BaseField
{
    protected $data;
    protected $type;
    protected $emptyValue = null;

    public $name;
    public $defaultValue;
    public $disabled;
    public $label;
    public $readonly;
    public $validation;
    public $value;
    public $roles;

    public function __construct(string $name, array $data = [])
    {
        $this->data = $data;
        $this->name = $name;

        $this->setPropertiesBasedOnData();
    }

    protected function setPropertiesBasedOnData()
    {
        $data = $this->data;

        $this->label = $data['label'] ?? null;
        $this->disabled = $data['disabled'] ?? false;
        $this->readonly = $data['readonly'] ?? false;
        $this->validation = $data['validation'] ?? [];
        $this->roles = $data['visibility']['roles'] ?? [];

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
            'is_readonly' => $this->readonly,
            'is_required' => $this->isRequired(),
            'default_value' => $this->defaultValue,
            'value' => $this->value,
        ];
    }

    public function validationRules(): array
    {
        $rules = $this->validation['rules'] ?? [];

        if (!$this->isRequired()) {
            $rules[] = 'nullable';
        }

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

    public function canBeAccessed(User $author = null): bool
    {
        if (!empty($this->roles)) {
            if (is_null($author)) {
                return false;
            }

            if (!$author->hasRole(config('permission.super_admin_role'))) {
                return $author->hasRole($this->roles);
            }
        }

        return true;
    }
}
