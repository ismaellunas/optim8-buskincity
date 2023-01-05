<?php

namespace Modules\FormBuilder\Fields;

use Propaganistas\LaravelPhone\PhoneNumber;

use Mews\Purifier\Facades\Purifier;

class Phone extends BaseField
{
    public function value(): mixed
    {
        if (isset($this->value['country']) && isset($this->value['number'])) {
            $phoneNumber = PhoneNumber::make(
                    $this->value['number'],
                    $this->value['country']
                )
                ->formatInternational();

            return Purifier::clean($phoneNumber);
        }

        return '-';
    }
}