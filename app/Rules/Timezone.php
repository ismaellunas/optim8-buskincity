<?php

namespace App\Rules;

use App\Services\CountryService;
use Illuminate\Contracts\Validation\InvokableRule;

class Timezone implements InvokableRule
{
    public function __invoke($attribute, $value, $fail)
    {
        if (
            !(is_null($value) || $value == '')
            && app(CountryService::class)
                ->getTimezoneOptions()
                ->doesntContain('id', $value)
        ) {
            $fail('validation.in')->translate();
        }
    }
}
