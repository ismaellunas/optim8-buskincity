<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public static function getFrontendCssUrl(): string
    {
        $urlCss = Setting::where('key', 'url_css')->first(['key', 'value']);

        return $urlCss->value ?? mix('css/app.css')->toHtml();
    }
}
