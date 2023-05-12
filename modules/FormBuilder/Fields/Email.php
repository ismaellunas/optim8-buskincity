<?php

namespace Modules\FormBuilder\Fields;

use Modules\FormBuilder\Contracts\MappableFieldInterface;

class Email extends BaseField implements MappableFieldInterface
{
    public static function mappingFieldTypes(): array
    {
        return [
            'Email',
            'Text',
            'Textarea',
        ];
    }

    public function getMappedValue(array $toField): mixed
    {
        $type = $toField['type'];

        if (! in_array($type, self::mappingFieldTypes())) {
            return null;
        }

        return $this->value;
    }
}
