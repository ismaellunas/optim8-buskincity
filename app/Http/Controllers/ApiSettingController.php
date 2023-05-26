<?php

namespace App\Http\Controllers;

use App\Services\SettingService;

class ApiSettingController extends Controller
{
    public function getValueByKey(string $key): mixed
    {
        return app(SettingService::class)->getMaxFileSize($key);
    }
}
