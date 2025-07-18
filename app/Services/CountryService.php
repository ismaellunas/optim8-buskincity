<?php

namespace App\Services;

use App\Entities\Caches\CountryCache;
use App\Models\Country;
use App\Models\UserMeta;
use App\Traits\HasCache;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CountryService
{
    use HasCache;

    private $timezones;

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
        $key = 'country_options';

        if (! $this->hasLoadedKey($key)) {
            $this->setLoadedKey(
                $key,
                app(CountryCache::class)
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
                    })
            );
        }

        return $this->getLoadedKey($key);
    }

    public function getUserCountryOptions(): Collection
    {
        $key = 'user_country_options';

        if (! $this->hasLoadedKey($key)) {
            $countries = UserMeta::key('country')
                ->select('value')
                ->distinct()
                ->pluck('value');

            $this->setLoadedKey(
                $key,
                Country::orderBy('display_name')
                    ->when($countries, function ($q, $countries) {
                        $q->whereIn('alpha2', $countries);
                    })
                    ->get([
                        'alpha2',
                        'display_name',
                    ])
                    ->map(function ($country) {
                        return [
                            'id' => $country->alpha2,
                            'value' => $country->display_name,
                        ];
                    })
            );
        }

        return $this->getLoadedKey($key);
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

    public function getTimezoneOptions(): Collection
    {
        if (! is_null($this->timezones)) {
            return $this->timezones;
        }

        $timezones = array();
        $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( DateTimeZone::ALL ) );

        $timezoneOffsets = array();
        foreach ($timezones as $timezone) {
            $tz = new DateTimeZone($timezone);
            $timezoneOffsets[$timezone] = $tz->getOffset(new DateTime());
        }

        asort($timezoneOffsets);

        $timezoneList = [];
        foreach ($timezoneOffsets as $timezone => $offset) {
            $offsetPrefix = $offset < 0 ? '-' : '+';
            $offsetFormatted = gmdate( 'G:i', abs($offset) );

            $prettyOffset = "GMT {$offsetPrefix}{$offsetFormatted}";

            $timezoneList[] = [
                'id' => $timezone,
                'value' => [
                    'offsetValue' => $prettyOffset,
                    'timezone' => Str::replace("_", " ", "$timezone"),
                ],
            ];
        }

        $this->timezones = collect($timezoneList);

        return $this->timezones;
    }
}
