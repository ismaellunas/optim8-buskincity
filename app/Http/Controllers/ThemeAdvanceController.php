<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
                'additionalCodes' => $this->settingService->getAdditionalCodes()
            ])
        );
    }

    public function update(Request $request)
    {
        $additionalCodes = $request->all();

        foreach ($additionalCodes as $key => $additionalCode) {
            $setting = Setting::firstOrNew(['key' => $key]);

            $setting->value = $additionalCode;

            $setting->save();

            $url = null;

            if (!empty($additionalCode)) {
                $asset = $this->settingService->uploadAdditionalCodeToCloudStorage(
                    $this->settingService->getAdditionalCodeFileName($key),
                    $additionalCode,
                    (!App::environment('production') ? config('app.env') : null)
                );

                $url = $asset->fileUrl;
            }

            $this->settingService->saveAdditionalCodeUrl($key, $url);
        }

        $this->settingService->clearStorageTheme();

        $this->generateFlashMessage($this->title.' updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
