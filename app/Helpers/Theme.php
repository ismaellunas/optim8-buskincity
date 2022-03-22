<?php

namespace App\Helpers;

use App\Services\SettingService;

class Theme
{
    public static function getLogoUrl(): ?string
    {
        $logoUrl = app(SettingService::class)->getLogoUrl();

        if (!$logoUrl || $logoUrl == '') {
            $logoUrl = 'https://dummyimage.com/48x28/e5e5e5/000000.png&text=B+752';
        }

        return $logoUrl;
    }
}
