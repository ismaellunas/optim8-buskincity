<?php

namespace App\Http\Controllers;

use App\Services\SettingService;

class ThemeFontSizeController extends CrudController
{
    protected $baseRouteName = 'admin.theme.font-size';
    protected $title = 'Font Size';

    private $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }
}
