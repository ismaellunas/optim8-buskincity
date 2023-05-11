<?php

namespace Modules\FormBuilder\Fields;

use Modules\FormBuilder\Contracts\MappableFieldInterface;

class Select extends BaseField implements MappableFieldInterface
{
    public $translateTo = [];

    public function value(): mixed
    {
        if (!$this->value) {
            return '-';
        }

        $option = collect($this->field['options'])
            ->where('id', $this->value)
            ->first();

        if ($option) {
            return htmlspecialchars($option['value']);
        }

        return '-';
    }

    public static function mappingFieldTypes(): array
    {
        return [
            'Select',
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
