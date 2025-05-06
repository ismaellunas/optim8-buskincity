<?php

namespace Modules\FormBuilder\Rules;

use Illuminate\Contracts\Validation\Rule;

class FieldNameRequired implements Rule
{
    private $type;

    public function passes($attribute, $value)
    {
        if ($value) {
            foreach ($value as $field) {
                if (empty($field['name'])) {
                    $this->type = $field['type'];

                    return false;
                }
            }
        }

        return true;
    }

    public function message()
    {
        return __('The :type field name is required.', [
            'type' => $this->type,
        ]);
    }
}
