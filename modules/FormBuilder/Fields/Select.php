<?php

namespace Modules\FormBuilder\Fields;

use Mews\Purifier\Facades\Purifier;

class Select extends BaseField
{
    public function value(): mixed
    {
        if (!$this->value) {
            return '-';
        }

        $option = collect($this->field['options'])
            ->where('id', $this->value)
            ->first();

        if ($option) {
            return Purifier::clean($option['value']);
        }

        return '-';
    }
}