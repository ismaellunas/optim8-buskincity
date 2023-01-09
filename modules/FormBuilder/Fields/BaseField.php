<?php

namespace Modules\FormBuilder\Fields;

use Mews\Purifier\Facades\Purifier;

class BaseField
{
    protected $field;
    protected $value;

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
        return Purifier::clean($this->value);
    }
}