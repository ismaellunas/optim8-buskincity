<?php

namespace App\Helpers;

use App\Services\SettingService;

class Theme
{
    public static function getLogoUrl(): string
    {
        return app(SettingService::class)->getLogoUrl();
    }
}
