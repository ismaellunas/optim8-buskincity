<?php

namespace App\Services;

use Exception;
use App\Entities\GeoIP\HttpClient;
use Torann\GeoIP\Services\AbstractService;

class IPRegistryService extends AbstractService
{
    protected $client;

    public function boot()
    {
        $this->client = new HttpClient([
            'base_uri' => 'https://api.ipregistry.co/',
            'query' => [
                'key' => $this->config('key'),
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

        return $this->hydrate($json);
    }
}