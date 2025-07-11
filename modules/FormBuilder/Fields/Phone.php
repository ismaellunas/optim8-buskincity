<?php

namespace Modules\FormBuilder\Fields;

use Modules\FormBuilder\Contracts\MappableFieldInterface;
use Propaganistas\LaravelPhone\PhoneNumber;

class Phone extends BaseField implements MappableFieldInterface
{
    private function phoneE164()
    {
        return (new PhoneNumber(
                $this->value['number'],
                $this->value['country']
            ))
            ->formatE164();
    }

    public function value(): mixed
    {
        if (isset($this->value['country']) && isset($this->value['number'])) {
            $phoneNumber = (new PhoneNumber(
                    $this->value['number'],
                    $this->value['country']
                ))
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

    public function getMappedValue(array $toField): mixed
    {
        $type = $toField['type'];

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
