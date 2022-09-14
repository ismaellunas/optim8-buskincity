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
        $validValues = collect($this->options)
            ->map(function ($option, $key) {
                if (isset($option['id'])) {
                    return $option['id'];
                }

                return $key;
            })
            ->all();

        $rules[$this->name][] = 'in:'.implode(',', $validValues);
    }

    public function validationRules(): array
    {
        $rules = parent::validationRules();

        $this->adjustInRule($rules);

        return $rules;
    }
}
