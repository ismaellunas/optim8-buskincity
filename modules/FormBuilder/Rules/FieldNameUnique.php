<?php

namespace Modules\FormBuilder\Rules;

use Illuminate\Contracts\Validation\Rule;

class FieldNameUnique implements Rule
{
    private $duplicateNames;

    public function passes($attribute, $value)
    {
        $fieldNames = collect();

        if ($value) {
            foreach ($value as $field) {
                $fieldNames->push($field['name']);
            }
        }

        $this->duplicateNames = $fieldNames->duplicates();

        return $fieldNames->duplicates()->isEmpty();
    }

    public function message()
    {
        return __('The :names field name must be unique and cannot match any existing field names.', [
            'names' => $this->duplicateNames->implode(', ')
        ]);
    }
}
