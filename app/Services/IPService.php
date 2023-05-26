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
        return $this->getClientData()->iso_code ?? $default;
    }

    public function getLanguageCode(): ?string
    {
        return $this->getClientData()->language_code ?? null;
    }

    public function getTimezone(): string
    {
        return $this->getClientData()->timezone ?? config('app.timezone');
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
            'latitude' => $this->getClientData()->lat ?? null,
            'longitude' => $this->getClientData()->lon ?? null,
        ];
    }

    public function getCity(string $default = null): ?string
    {
        return $this->getClientData()->city ?? $default;
    }

    public function getDateTime(): Carbon
    {
        return Carbon::now($this->getTimezone());
    }
}
