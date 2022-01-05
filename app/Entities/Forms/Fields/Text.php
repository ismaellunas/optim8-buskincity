<?php

namespace App\Entities\Forms\Fields;

class Text extends BaseField
{
    protected $type = "Text";

    public $maxlength;
    public $placeholder;

    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->maxlength = $data['maxlength'] ?? null;
        $this->placeholder = $data['placeholder'] ?? null;
    }

    public function schema(): array
    {
        $schema = [
            'maxlength' => $this->maxlength ?? $this->max() ?? null,
            'placeholder' => $this->placeholder,
        ];

        return array_merge(parent::schema(), $schema);
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
