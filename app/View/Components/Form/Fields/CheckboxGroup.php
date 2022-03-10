<?php

namespace App\View\Components\Form\Fields;

use Illuminate\Support\Str;

class CheckboxGroup extends BaseField
{
    public function valueReadable($value)
    {
        $value = Str::replace('_', ' ', $value);

        return Str::title($value);
    }
}
