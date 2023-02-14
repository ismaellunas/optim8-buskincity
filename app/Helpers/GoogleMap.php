<?php

namespace App\Helpers;

class GoogleMap
{
    public static function directionUrl(
        string|float $latitude,
        string|float $longitude,
        string|float $originLatitude = null,
        string|float $originLongitude = null,
    ): string {
        $urlParts = [
            "https://www.google.com/maps/dir/?api=1",
            "&destination=".urlencode($latitude).','.urlencode($longitude),
        ];

        if ($originLatitude && $originLongitude) {
            $urlParts[] = "&origin=".urlencode($originLatitude).','.urlencode($originLongitude);
        }

        return implode("", $urlParts);
    }
}
