<?php

namespace App\Actions\Fortify;

trait PasswordValidationWithoutConfirmationRules
{
    use PasswordValidationRules {
        PasswordValidationRules::passwordRules as parentPasswordRules;
    }

    protected function passwordRules()
    {
        $rules = $this->parentPasswordRules();

        if (($key = array_search('confirmed', $rules)) !== false) {
            unset($rules[$key]);
        }

        return $rules;
    }
}
