<?php

namespace App\Http\Controllers;

use App\Services\SettingService;

class ApiSettingController extends Controller
{
    public function maxFileSize(): int
    {
        return SettingService::maxFileSize();
    }
}
