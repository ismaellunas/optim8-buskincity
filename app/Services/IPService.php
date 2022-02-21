<?php

namespace App\Services;

class IPService
{
    private function getUserIp()
    {
        // It will get IP version 4 or 6
        // https://stackoverflow.com/questions/51245053/how-can-i-get-both-ipv4-and-ipv6-address-using-php-code
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED"];
        } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_FORWARDED"])) {
            $ip = $_SERVER["HTTP_FORWARDED"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }

        if (strpos($ip, ',') > 0) {
            $ip = substr($ip, 0, strpos($ip, ','));
        }

        return $ip;
    }

    public function getUserData()
    {
        return geoip($this->getUserIp());
    }
}