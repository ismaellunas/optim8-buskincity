<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeColorRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

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

        Log::info('Controller - Before generate new style');

        try {
            $this->generateNewStyleProcess();
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }

        Log::info('Controller - After generate new style');

        $this->generateFlashMessage('Colors updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
