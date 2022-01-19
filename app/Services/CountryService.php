<?php

namespace App\Services;

use App\Entities\Caches\CountryCache;
use App\Models\Country;
use Illuminate\Support\Collection;

class CountryService
{
    public function getPhoneCountryOptions(): Collection
    {
        return app(CountryCache::class)
            ->remember('phone_options', function () {
                return Country::whereNotNull('dial')
                    ->orderBy('display_name')
                    ->get([
                        'alpha2',
                        'dial',
                        'display_name',
                    ])
                    ->map(function ($country) {
                        return [
                            'id' => $country->alpha2,
                            'value' => $country->display_name,
                            'dial' => $country->dial,
                        ];
                    });
            });
    }
}
