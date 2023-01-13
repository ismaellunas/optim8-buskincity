<?php

namespace App\Services;

use Carbon\Carbon;

class IPService
{
    private $clientData;

    public function getClientIp()
    {
        $ipaddress = '';

        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            if (isset($_SERVER['HTTP_CLIENT_IP']))
                $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
            else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
                $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            else if(isset($_SERVER['HTTP_X_FORWARDED']))
                $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
            else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
                $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
            else if(isset($_SERVER['HTTP_FORWARDED']))
                $ipaddress = $_SERVER['HTTP_FORWARDED'];
            else if(isset($_SERVER['REMOTE_ADDR']))
                $ipaddress = $_SERVER['REMOTE_ADDR'];
            else
                $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }

    private function isLocalhost(): bool
    {
        return $this->getClientIp() == '127.0.0.1';
    }

    public function getClientData(): mixed
    {
        if (is_null($this->clientData)) {
            $this->clientData = geoip($this->getClientIp());
        }

        return $this->clientData;
    }

    public function getCountryCode(string $default = null): ?string
    {
        return $this->getClientData()['location']['country']['code'] ?? $default;
    }

    public function getLanguageCode(): ?string
    {
        return $this->getClientData()['location']['language']['code'];
    }

    public function getTimezone(): ?string
    {
        return $this->getClientData()['time_zone']['id'] ?? config('app.timezone');
    }

    public function getGeoLocation(): ?array
    {
        if ($this->isLocalhost()) {
            return [
                'latitude' => 51.507351,
                'longitude' => -0.127758,
            ];
        }

        return [
            'latitude' => $this->getClientData()['location']['longitude'],
            'longitude' => $this->getClientData()['location']['latitude'],
        ];
    }

    public function getCity(string $default = null): ?string
    {
        return data_get($this->getClientData(), 'location.city') ?? $default;
    }

    public function getDateTime(): Carbon
    {
        $currentTime = data_get($this->getClientData(), 'time_zone.current_time') ?? null;

        return new Carbon($currentTime);
    }
}