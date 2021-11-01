<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HeaderController extends CrudController
{
    private $settingService;
    protected $baseRouteName = 'admin.theme.header';
    protected $componentName = 'ThemeHeader/Header/';

    public function __construct(SettingService $settingService)
    {
        $this->authorizeResource(Setting::class, 'setting');

        $this->settingService = $settingService;
    }

    public function index()
    {
        return Inertia::render($this->componentName.'Index',[
            'baseRouteName' => $this->baseRouteName,
            'headerLayoutLastSaved' => $this->settingService->getHeaderLayoutLastSaved(),
            'headerLogoUrlLastSaved' => $this->settingService->getHeaderLogoUrlLastSaved(),
            'settings' => $this->settingService->getHeader(),
        ]);
    }

    public function updateLayout(Request $request)
    {
        $layout = $request->layout;

        $setting = Setting::firstOrNew(['key' => 'header_layout']);
        $setting->value = $layout;
        $setting->save();

        $this->generateFlashMessage('Header layout updated successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    public function updateLogo(Request $request)
    {
        $inputs = $request->all();

        $upload = $this->settingService->uploadLogoToCloudStorage($inputs);
        $setting = Setting::firstOrNew(['key' => 'header_logo_url']);
        $setting->display_name = $upload->fileName;
        $setting->value = $upload->fileUrl;
        $setting->save();

        $this->generateFlashMessage('Header logo upload successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }
}
