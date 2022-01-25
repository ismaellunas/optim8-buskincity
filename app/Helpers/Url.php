<?php

namespace App\Helpers;

use Illuminate\Routing\Route;

class Url
{
    public static function getPath($url): ?string
    {
        return parse_url($url, PHP_URL_PATH);
    }

    public static function getRoute(String $url): ?Route
    {
        return app('router')
            ->getRoutes($url)
            ->match(app('request')
            ->create($url));
    }
}
