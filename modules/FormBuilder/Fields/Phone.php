<?php

namespace Modules\FormBuilder\Fields;

use Modules\FormBuilder\Contracts\MappableFieldInterface;
use Propaganistas\LaravelPhone\PhoneNumber;

class Phone extends BaseField implements MappableFieldInterface
{
    private function phoneE164()
    {
        return PhoneNumber::make(
            $this->value['number'],
            $this->value['country']
        )->formatE164();
    }

    public function value(): mixed
    {
        if (isset($this->value['country']) && isset($this->value['number'])) {
            $phoneNumber = PhoneNumber::make(
                    $this->value['number'],
                    $this->value['country']
                )
                ->formatInternational();

            return $phoneNumber;
        }

        return '-';
    }

    public static function mappingFieldTypes(): array
    {
        return [
            'Phone',
            'Text',
            'Textarea',
        ];
    }

    public function getMappedValue(string $type, array $translateTo = []): mixed
    {
        if (!in_array($type, self::mappingFieldTypes())) {
            return null;
        }

        try {
            $e164 = $this->phoneE164();
        } catch (\Throwable $th) {
            $e164 = null;
        }

        if ($type == 'Phone') {
            $phoneParts = [];
            $phoneParts[':fieldname:'] = $this->value;
            $phoneParts[':fieldname:_e164'] = $e164;

            return $phoneParts;
        }

        return $e164;
    }
}
