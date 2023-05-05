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

    public function getMappedValue(string $toType): mixed
    {
        if (! in_array($toType, self::mappingFieldTypes())) {
            return null;
        }

        if ($toType == 'Country') {
            return $this->value;
        }

        return $this->value();
    }
}
