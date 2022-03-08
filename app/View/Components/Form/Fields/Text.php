<?php

namespace App\View\Components\Form\Fields;

class Text extends BaseField
{
    public function __construct($label, $value, $translate, $userLocale)
    {
        parent::__construct($label, $value, $translate, $userLocale);

        $this->value = $this->setValueTranslation($value);
    }
}
