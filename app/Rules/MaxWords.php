<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class MaxWords implements Rule
{
    protected $max;

    public function __construct($max)
    {
        $this->max = $max;
    }

    public function passes($attribute, $value)
    {
        return Str::wordCount($value) <= $this->max;
    }

    public function message()
    {
        return __('validation.max.word', ['max' => $this->max]);
    }
}
