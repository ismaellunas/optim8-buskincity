<?php

namespace Modules\FormBuilder\Fields;

use App\Services\CountryService;

class Country extends BaseField
{
    public function value(): mixed
    {
        return app(CountryService::class)->getCountryName($this->value);
    }
}