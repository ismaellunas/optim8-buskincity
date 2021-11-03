<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThemeFooterController extends CrudController
{
    private $settingService;
    private $modelSetting = Setting::class;

    protected $baseRouteName = 'admin.theme.footer';
    protected $componentName = 'ThemeFooter/';
    protected $title = "Footer";

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function edit()
    {
        return Inertia::render(
            $this->componentName.'Edit',
            $this->getData([
                'settings' => $this->settingService->getFooters(),
            ]),
        );
    }

    public function updateLayout(Request $request)
    {
        $layout = $request->layout;

        $setting = Setting::firstOrNew(['key' => 'footer_layout']);
        $setting->value = $layout;
        $setting->save();

        $this->generateFlashMessage('Footer layout updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }

    public function destroy($id)
    {
        //
    }
}
