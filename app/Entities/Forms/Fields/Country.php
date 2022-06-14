<?php

namespace App\Entities\Forms\Fields;

use App\Services\CountryService;

class Country extends Select
{
    public function __construct(string $name, array $data = [])
    {
        parent::__construct($name, $data);

        $this->options = $this->getOptions();
    }

    private function getOptions(): array
    {
        return app(CountryService::class)
            ->getCountryOptions()
            ->flatMap(function ($country) {
                return [
                    $country['id'] => $country['value']
                ];
            })
            ->all();
    }
}
