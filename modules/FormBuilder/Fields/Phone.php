<?php

namespace Modules\FormBuilder\Fields;

use Propaganistas\LaravelPhone\PhoneNumber;

class Phone
{
    protected $field;
    protected $value;

    public function __construct(array $field, ?array $value = [])
    {
        $this->field = $field;
        $this->value = $value;
    }

    public function value()
    {
        if (isset($this->value['country']) && isset($this->value['number'])) {
            return PhoneNumber::make(
                    $this->value['number'],
                    $this->value['country']
                )
                ->formatInternational();
        }

        return '-';
    }
}