<?php

namespace Modules\FormBuilder\Fields;

use App\Services\CountryService;

class Country extends BaseField
{
    public function value(): mixed
    {
        if ($this->value) {
            return app(CountryService::class)->getCountryName($this->value);
        }

        return $this->value;
    }
}