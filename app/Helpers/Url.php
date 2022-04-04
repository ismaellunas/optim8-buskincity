<?php

namespace App\Helpers;

use Illuminate\Routing\Route;
use Illuminate\Support\Str;

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

    public static function generateUniqueSegment(): string
    {
        $uniqPart = abs(crc32(uniqid('', true)));
        $uniqPart = substr($uniqPart, 0, random_int(6, 9));

        return Str::padLeft($uniqPart, 6, 0);
    }
}
