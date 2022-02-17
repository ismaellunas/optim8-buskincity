<?php

namespace App\View\Components\Form\Fields;

use Propaganistas\LaravelPhone\PhoneNumber;

class Phone extends BaseField
{
    public $label;
    public $phoneNumber = null;

    public function __construct($label, $value, $translate, $userLocale)
    {
        parent::__construct($label, $value, $translate, $userLocale);

        if ($value) {
            if ($value['number']) {
                $this->phoneNumber = $this->setPhoneNumber($value);
            }
        }
    }

    private function setPhoneNumber(array $value): string
    {
        return PhoneNumber::make(
            $value['number'],
            $value['country']
        );
    }
}
