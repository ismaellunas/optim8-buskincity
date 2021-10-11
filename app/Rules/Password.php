<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Laravel\Fortify\Rules\Password as FortifyPassword;

class Password extends FortifyPassword implements Rule
{
    protected $requireUppercase = true;

    protected $requireNumeric = true;
}
