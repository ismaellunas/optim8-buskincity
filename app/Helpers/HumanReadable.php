<?php

namespace App\Helpers;

use Carbon\Carbon;
use Propaganistas\LaravelPhone\PhoneNumber;
use App\Services\IPService;

class HumanReadable
{
    public static function bytesToHuman($bytes)
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

        for ($i = 0; $bytes >= 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public static function timestampToDateTime(int $timestamp): string
    {
        return Carbon::parse($timestamp)
            ->format(config('constants.format.date_time'));
    }

    public static function dateTimeByUserTimezone(Carbon $dateTime, string $format = null): string
    {
        $format = $format ?? config('constants.format.date_time');
        $timezone = app(IPService::class)->getTimezone();

        return $dateTime->setTimezone($timezone)->format($format);
    }

    public static function phoneNumberFormat(string $number, string $country)
    {
        return (new PhoneNumber($number, $country))->formatInternational();
    }
}
