<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeColorRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

class ThemeColorController extends CrudController
{
    protected $baseRouteName = 'admin.theme.color';
    protected $title = 'Colors';

    private $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function edit()
    {
        $defaultColors = config('constants.theme_colors');

        return Inertia::render(
            'ThemeColor/Edit',
            $this->getData([
                'defaultColors' => $defaultColors,
                'colors' => $this->settingService->getColors(),
            ])
        );
    }

    public function update(ThemeColorRequest $request)
    {
        $colors = $request->all();

        foreach ($colors as $key => $color) {
            $setting = Setting::firstOrNew(['key' => $key]);
            $setting->value = $color;
            $setting->save();
        }

        $this->settingService->generateVariablesSass();

        $this->settingService->generateThemeCss();

        $asset = $this->settingService->uploadThemeCssToCloudStorage(
            !App::environment('production')
            ? config('app.env')
            : null
        );

        $this->settingService->saveCssUrl($asset->fileUrl);

        $this->settingService->clearStorageTheme();

        $this->generateFlashMessage('Colors updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
