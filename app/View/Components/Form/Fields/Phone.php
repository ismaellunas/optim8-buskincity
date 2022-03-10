<?php

namespace App\View\Components\Form\Fields;

use Propaganistas\LaravelPhone\PhoneNumber;

class Phone extends BaseField
{
    public $label;
    public $phoneNumber = null;

    public function __construct(array $field)
    {
        parent::__construct($field);

        if ($this->value) {
            if (isset($this->value['number'])) {
                $this->phoneNumber = $this->getPhoneNumberFormat($this->value);
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
