<?php

namespace App\Helpers;

class Url
{
    public static function getPath($url): ?string
    {
        return parse_url($url, PHP_URL_PATH);
    }
}
