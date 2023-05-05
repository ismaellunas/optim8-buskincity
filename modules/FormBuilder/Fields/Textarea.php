<?php

namespace Modules\FormBuilder\Fields;

use Modules\FormBuilder\Contracts\MappableFieldInterface;

class Textarea extends BaseField implements MappableFieldInterface
{
    public $translateTo = [];

    public static function mappingFieldTypes(): array
    {
        return ['Textarea'];
    }

    public function getMappedValue(string $type): mixed
    {
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
