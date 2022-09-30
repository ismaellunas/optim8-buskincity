<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeFontSizeRequest;
use App\Jobs\CompileThemeCss;
use App\Models\Setting;
use App\Services\SettingService;
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
            'ThemeFontSize',
            $this->getData([
                'defaultFontSizes' => $defaultFontSizes,
                'fontSizes' => $this->settingService->getFontSizes(),
            ])
        );
    }

    public function update(ThemeFontSizeRequest $request)
    {
        $fontSizes = $request->all();

        foreach ($fontSizes as $key => $fontSize) {
            $setting = Setting::firstOrNew(['key' => $key]);
            $setting->value = $fontSize;
            $setting->save();
        }

        CompileThemeCss::dispatch();

        $this->generateFlashMessage('Font Sizes updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
