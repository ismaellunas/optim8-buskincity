<?php

namespace Modules\FormBuilder\Fields;

use Mews\Purifier\Facades\Purifier;
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
            return Purifier::clean($option['value'], 'form_builder');
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
