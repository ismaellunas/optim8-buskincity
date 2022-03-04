<?php

namespace App\View\Components\Form\Fields;

class Text extends ViewBaseField
{
    public function __construct($label, $value, $isTranslated, $userLocale)
    {
        parent::__construct($label, $value, $isTranslated, $userLocale);

        $this->value = $this->setValueTranslation($value);
    }
}
