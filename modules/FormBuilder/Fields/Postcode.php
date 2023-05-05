<?php

namespace Modules\FormBuilder\Fields;

use Modules\FormBuilder\Contracts\MappableFieldInterface;

class Postcode extends BaseField implements MappableFieldInterface
{
    public static function mappingFieldTypes(): array
    {
        return [
            'Postcode',
            'Text',
            'Textarea',
        ];
    }

    public function getMappedValue(string $type, array $translateTo = []): mixed
    {
        if (! in_array($type, self::mappingFieldTypes())) {
            return null;
        }

        return $this->value;
    }
}
