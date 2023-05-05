<?php

namespace Modules\FormBuilder\Fields;

use Modules\FormBuilder\Contracts\MappableFieldInterface;

class Number extends BaseField implements MappableFieldInterface
{
    public static function mappingFieldTypes(): array
    {
        return [
            'Number',
            'Text',
            'Textarea',
        ];
    }

    public function getMappedValue(string $type): mixed
    {
        if (! in_array($type, self::mappingFieldTypes())) {
            return null;
        }

        return $this->value;
    }
}
