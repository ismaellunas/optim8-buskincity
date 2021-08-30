<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AlphaNumericDash implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (! ($value == null || $value == '')) {
            return preg_match('/^[a-zA-Z0-9-]+$/', $value) > 0;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.alpha_numeric_dash');
    }
}
