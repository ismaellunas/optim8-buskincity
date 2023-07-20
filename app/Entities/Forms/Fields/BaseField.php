<?php

namespace App\Entities\Forms\Fields;

use App\Models\User;

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
    public $storedValue;
    public $entity;
    public $notes = [];
    public $column;

    public $model;

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

        if (array_key_exists('notes', $data)) {
            $this->notes = $this->filterNotes($data['notes']);
        }

        if (array_key_exists('column', $data)) {
            $this->column = $data['column'];
        }

        $this->value = $data['value'] ?? $this->defaultValue;
    }

    protected function isRequired(): bool
    {
        if (!empty($this->validation['rules'])) {
            return array_key_exists('required', $this->validation['rules'])
                && $this->validation['rules']['required'];
        }

        return false;
    }

    protected function adjustNullableRule(&$rules)
    {
        if (!$this->isRequired()) {
            $rules[$this->name][] = 'nullable';
        }
    }

    protected function getSchemaValue(): mixed
    {
        return $this->storedValue ?? $this->value;
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
            'instructions' => $this->getInstructions(),
            'value' => $this->getSchemaValue(),
            'notes' => $this->notes,
            'column' => $this->column,
        ];
    }

    public function validationRules(): array
    {
        $rules = [];

        $rules[$this->name] = $this->validation['rules'] ?? [];

        $this->transformToFlatten($rules);
        $this->adjustNullableRule($rules);

        return $rules;
    }

    public function validationMessages(): array
    {
        return $this->validation['messages'] ?? [];
    }

    public function validationAttributes(array $inputs = []): array
    {
        return $this->getLabels($inputs);
    }

    public function canBeAccessed(User $author = null): bool
    {
        if (!empty($this->roles)) {
            if (is_null($author)) {
                return false;
            }

            if (
                !($author->hasRole(config('permission.role_names.super_admin'))
                || $author->hasRole(config('permission.role_names.admin')))
            ) {
                return $author->hasRole($this->roles);
            }
        }

        return true;
    }

    public function getDataToBeSaved(array $inputs): array
    {
        $data = [];

        if (array_key_exists($this->name, $inputs)) {
            $data[$this->name] = $inputs[$this->name];
        }

        return $data;
    }

    public function getLabels(array $inputs = []): array
    {
        return [
            $this->name =>  $this->label,
        ];
    }

    public function getInstructions(): array
    {
        return [];
    }

    public function findStoredValue(array $storedValues = []): mixed
    {
        if (array_key_exists($this->name, $storedValues)) {
            return $storedValues[$this->name];
        }

        return null;
    }

    protected function transformToFlatten(&$rules)
    {
        $rules = collect($rules)->transform(function ($rule) {
            $newRules = [];

            foreach ($rule as $validationName => $validationValue) {
                if (is_object($validationValue)) {
                    $newRules[] = $validationValue;
                } else if (is_bool($validationValue)) {
                    if ($validationValue) {
                        $newRules[] = $validationName;
                    }
                } else if (is_array($validationValue)) {
                    $newRules[] = $validationName.':'.implode(',', $validationValue);
                } else {
                    if ($validationValue) {
                        $newRules[] = $validationName.':'.$validationValue;
                    }
                }
            }

            return $newRules;
        })->all();
    }

    private function filterNotes(array $notes): array
    {
        return collect($notes)
            ->filter()
            ->values()
            ->all();
    }
}
