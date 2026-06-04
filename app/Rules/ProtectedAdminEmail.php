<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class ProtectedAdminEmail implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (! is_string($value) || $value === '') {
            return true;
        }

        $roles = explode('|', config('permission.admin_or_super_admin'));

        return ! User::email($value)->inRoleNames($roles)->exists();
    }

    public function message(): string
    {
        return __('validation.email_belongs_to_protected_user');
    }
}
