<?php

namespace Modules\FormBuilder\Fields;

use App\Services\CountryService;
use Modules\FormBuilder\Contracts\MappableFieldInterface;

class Country extends BaseField implements MappableFieldInterface
{
    public function value(): mixed
    {
        if ($this->value) {
            return app(CountryService::class)->getCountryName($this->value);
        }

        return $this->value;
    }

    public static function mappingFieldTypes(): array
    {
        return [
            'Country',
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

        if ($type == 'Country') {
            return $this->value;
        }

        return $this->value();
    }
}
