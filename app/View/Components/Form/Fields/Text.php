<?php

namespace App\View\Components\Form\Fields;

use App\Contracts\ViewFieldInterface;
use App\Traits\ViewBaseField;

class Text extends TranslatableField implements ViewFieldInterface
{
    use ViewBaseField;
}
