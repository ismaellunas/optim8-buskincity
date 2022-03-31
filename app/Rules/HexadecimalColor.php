<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class HexadecimalColor implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (! ($value == null || $value == '')) {
            return preg_match('/^\#[\da-f]{6}$/i', $value) > 0;
        }
        return true;
    }

    public function message(): string
    {
        return trans('validation.hexadecimal_color');
    }
}
