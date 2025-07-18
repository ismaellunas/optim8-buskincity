<?php

namespace Modules\FormBuilder\Fields;

class BaseField
{
    protected $field;

    public $value;

    public function __construct(array $field = [], mixed $value = null)
    {
        $this->field = $field;
        $this->value = $value;
    }

    public function getSavedData(mixed $value): mixed
    {
        return $value;
    }

    public function value(): mixed
    {
        if ($this->value) {
            return htmlspecialchars($this->value);
        }
        return null;
    }

    public function componentValue(): array
    {
        return [
            'component' => null,
            'value' => $this->value()
        ];
    }
}
