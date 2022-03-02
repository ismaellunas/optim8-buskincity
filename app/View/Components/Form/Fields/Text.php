<?php

namespace App\View\Components\Form\Fields;

class Text extends BaseField
{
    public function __construct($label, $value, $isTranslated, $userLocale)
    {
        parent::__construct($label, $value, $isTranslated, $userLocale);

        $this->value = $this->setValueTranslation($value);
    }

    protected function getViewName(): string
    {
        return "base-field";
    }
}
