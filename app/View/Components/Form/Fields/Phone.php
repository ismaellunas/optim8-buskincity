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
                $this->phoneNumber = $this->getPhoneNumberFormat($value);
            }
        }
    }

    private function getPhoneNumberFormat(array $phoneNumber): string
    {
        return PhoneNumber::make(
                $phoneNumber['number'],
                $phoneNumber['country']
            )
            ->formatInternational();
    }
}
