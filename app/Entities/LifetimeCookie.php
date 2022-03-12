<?php

namespace App\Entities;

class LifetimeCookie
{
    private $expires = 0;

    public function __construct()
    {
        // https://stackoverflow.com/questions/3290424/set-a-cookie-to-never-expire
        $this->expires = time() + (10 * 365 * 24 * 60 * 60);
    }

    public function set(
        string $key,
        string $value,
        string $path = '/'
    ): void {
        setcookie($key, $value, $this->expires, $path);
    }

    public function get(string $key): ?string {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
    }
}