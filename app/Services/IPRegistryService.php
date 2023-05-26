<?php

namespace App\Services;

use App\Entities\GeoIP\HttpClient;
use App\Services\SettingService;
use Exception;
use Illuminate\Support\Arr;
use Torann\GeoIP\Services\AbstractService;

class IPRegistryService extends AbstractService
{
    protected $client;

    public function boot()
    {
        $this->client = new HttpClient([
            'base_uri' => 'https://api.ipregistry.co/',
            'query' => [
                'key' => app(SettingService::class)->getIpRegistryApi(),
            ],
        ]);
    }

    public function locate($ip)
    {
        // Get data from client
        $data = $this->client->get($ip);

        // Verify server response
        if ($this->client->getErrors() !== null) {
            throw new Exception('Request failed (' . $this->client->getErrors() . ')');
        }

        // Parse body content
        $json = json_decode($data[0], true);

        return $this->hydrate([
            'ip' => $ip,
            'iso_code' => Arr::get($json, 'location.country.code'),
            'country' => Arr::get($json, 'location.country.name'),
            'city' => Arr::get($json, 'location.city'),
            'state' => Arr::get($json, 'location.region.code'),
            'state_name' => Arr::get($json,'location.region.name'),
            'postal_code' => Arr::get($json, 'location.postal'),
            'lat' => Arr::get($json, 'location.latitude'),
            'lon' => Arr::get($json, 'location.longitude'),
            'timezone' => Arr::get($json, 'time_zone.id'),
            'continent' => Arr::get($json, 'location.continent.code'),
            'currency' => Arr::get($json, 'currency.code'),
            'language_code' => Arr::get($json, 'location.language.code'),
        ]);
    }
}
