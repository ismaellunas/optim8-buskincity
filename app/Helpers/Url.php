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

    public static function randomDigitSegment(callable $isExisting = null): ?string
    {
        $canContinueLoop = function ($code) use ($isExisting) {
            if (is_callable($isExisting)) {
                return $isExisting($code);
            }
            return false;
        };

        do {
            $code = random_int(100000, 999999);
        } while ($canContinueLoop($code));

        return $code;
    }
}
