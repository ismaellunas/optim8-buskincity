<?php

namespace Modules\FormBuilder\Fields;

use Modules\FormBuilder\Contracts\MappableFieldInterface;

class Text extends BaseField implements MappableFieldInterface
{
    public $translateTo = [];

    public static function mappingFieldTypes(): array
    {
        return [
            'Text',
            'Textarea',
            'Video',
        ];
    }

    public function getMappedValue(array $toField): mixed
    {
        $type = $toField['type'];

        if (! in_array($type, self::mappingFieldTypes())) {
            return null;
        }

        if ($this->translateTo && is_array($this->translateTo)) {
            $value = [];

            foreach ($this->translateTo as $language) {
                $value[$language] = $this->value;
            }

            return $value;
        }

        return $this->value;
    }
}
