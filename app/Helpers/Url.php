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
        $uniqParts = explode('.', uniqid('', true));

        $prefix = Str::substr(base_convert($uniqParts[1], 10, 16), -4, 4);

        return (
            Str::padLeft($prefix, 4, 0).
            Str::padLeft($uniqParts[0], 16, 0)
        );
    }
}
