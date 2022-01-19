<?php

namespace App\Entities\Forms\Fields;

class CheckboxGroup extends BaseField
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

    public function validationRules(): array
    {
        $rules = parent::validationRules();

        $rules[$this->name][] = 'array';

        $rules[$this->name.".*"][] = 'in:'.implode(',', array_keys($this->options));

        return $rules;
    }
}
