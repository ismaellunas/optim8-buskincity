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

    public function getCountryOptions(): Collection
    {
        return app(CountryCache::class)
            ->remember('country_options', function () {
                return Country::orderBy('display_name')
                    ->get([
                        'alpha2',
                        'display_name',
                    ])
                    ->map(function ($country) {
                        return [
                            'id' => $country->alpha2,
                            'value' => $country->display_name,
                        ];
                    });
            });
    }

    public function getCountryName(string $alpha2): string
    {
        $country = $this->getCountryOptions()
            ->where('id', $alpha2)
            ->first();

        if ($country) {
            return $country['value'];
        }

        return "";
    }
}
