<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeAdvanceRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Inertia\Inertia;

class ThemeAdvanceController extends ThemeOptionController
{
    protected $baseRouteName = 'admin.theme.advance';
    protected $title = 'Advanced';

    private $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function edit()
    {
        return Inertia::render(
            'ThemeAdvance',
            $this->getData([
                'trackingCodes' => $this->settingService->getTrackingCodes(),
                'additionalCodes' => $this->settingService->getAdditionalCodes()
            ])
        );
    }

    public function update(ThemeAdvanceRequest $request)
    {
        foreach ($request->validated() as $key => $code) {
            $setting = Setting::firstOrNew(['key' => $key]);

            $setting->value = $code;

            $setting->save();
        }

        $this->settingService->clearStorageTheme();

        $this->generateFlashMessage($this->title.' updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
