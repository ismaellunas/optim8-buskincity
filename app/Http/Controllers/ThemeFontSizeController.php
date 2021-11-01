<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeFontSizeRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;

class ThemeFontSizeController extends CrudController
{
    protected $baseRouteName = 'admin.theme.font-size';
    protected $title = 'Font Size';

    private $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function edit()
    {
        $defaultFontSizes = config('constants.theme_font_sizes');

        return Inertia::render(
            'ThemeFontSize/Edit',
            $this->getData([
                'defaultFontSizes' => $defaultFontSizes,
                'fontSizes' => $this->settingService->getFontSizes(),
            ])
        );
    }

    public function update(ThemeFontSizeRequest $request)
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

        $this->generateFlashMessage('Font Sizes updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
