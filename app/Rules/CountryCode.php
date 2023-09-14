<?php

namespace App\Rules;

use App\Services\CountryService;
use Illuminate\Contracts\Validation\InvokableRule;

class CountryCode implements InvokableRule
{
    public function __invoke($attribute, $value, $fail)
    {
        if (app(CountryService::class)
            ->getCountryOptions()
            ->doesntContain('id', strtoupper($value))
        ) {
            $fail('validation.in')->translate();
        }
    }
}
