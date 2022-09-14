<?php

namespace Modules\FormBuilder\Fields;

class Select
{
    protected $field;
    protected $value;

    public function __construct(array $field, ?string $value = null)
    {
        $this->field = $field;
        $this->value = $value;
    }

    public function value()
    {
        if ($this->value) {
            return '-';
        }

        $option = collect($this->field['options'])
            ->where('id', $this->value)
            ->first();

        if ($option) {
            return $option['value'];
        }

        return '-';
    }
}