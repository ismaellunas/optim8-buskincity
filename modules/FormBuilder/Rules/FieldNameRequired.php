<?php

namespace Modules\FormBuilder\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class FieldNameRequired implements Rule
{
    private $type;
    private $label;

    public function passes($attribute, $value)
    {
        if ($value) {
            foreach ($value as $field) {
                if (empty($field['name'])) {
                    $this->type = $field['type'];
                    $this->label = $field['label'];

                    return false;
                }
            }
        }

        return true;
    }

    public function message()
    {
        return __('The field name configuration for the :field field is required.', [
            'field' => Str::lower($this->label ?? $this->type),
        ]);
    }
}
