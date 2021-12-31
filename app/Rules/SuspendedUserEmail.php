<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class SuspendedUserEmail implements Rule
{
    public function passes($attribute, $value): bool
    {
        return ! User::email($value)->where('is_suspended', true)->exists();
    }

    public function message(): string
    {
        return __('Your Account is suspended, please contact the support.');
    }
}
