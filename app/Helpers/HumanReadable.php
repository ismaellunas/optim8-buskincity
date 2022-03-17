<?php

namespace App\Helpers;

use Carbon\Carbon;

class HumanReadable
{
    public static function bytesToHuman($bytes)
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public static function timestampToDateTime(int $timestamp): string
    {
        return Carbon::parse($timestamp)
            ->format(config('constants.format.date_time'));
    }
}
