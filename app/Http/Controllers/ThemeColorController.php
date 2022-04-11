<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeColorRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Inertia\Inertia;

class ThemeColorController extends ThemeOptionController
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
            'ThemeColor',
            $this->getData([
                'defaultColors' => $defaultColors,
                'colors' => $this->settingService->getColors(),
            ])
        );
    }

    public function update(ThemeColorRequest $request)
    {
        $colors = $request->validated();

        foreach ($colors as $key => $color) {
            $setting = Setting::firstOrNew(['key' => $key]);
            $setting->value = $color;
            $setting->save();
        }

        $this->generateNewStyleProcess($this->settingService);

        $this->generateFlashMessage('Colors updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
