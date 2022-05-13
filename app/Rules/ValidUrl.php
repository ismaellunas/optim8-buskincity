<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\URL;

class ValidUrl implements Rule
{
    public function passes($attribute, $value): bool
    {
        if ($value) {
            return URL::isValidUrl($value);
        }
        return true;
    }

    public function message(): string
    {
        return __('validation.url');
    }
}
