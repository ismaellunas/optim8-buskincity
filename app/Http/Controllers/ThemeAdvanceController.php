<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeAdvanceRequest;
use App\Models\Setting;
use App\Services\MenuService;
use App\Services\SettingService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ThemeAdvanceController extends ThemeOptionController
{
    protected $baseRouteName = 'admin.theme.advance';
    protected $title = 'Advanced';

    private $settingService;
    private $menuService;

    public function __construct(SettingService $settingService, MenuService $menuService)
    {
        $this->settingService = $settingService;
        $this->menuService = $menuService;
    }

    public function edit()
    {
        $pageOptions = $this->menuService->getPageOptions();
        $pageOptions[] = [
            'id' => null,
            'value' => 'Default',
            'locales' => null,
        ];

        return Inertia::render(
            'ThemeAdvance',
            $this->getData([
                'trackingCodes' => $this->settingService->getTrackingCodes(),
                'additionalCodes' => $this->settingService->getAdditionalCodes(),
                'homePageId' => $this->settingService->getHomePage(),
                'pageOptions' => $pageOptions,
            ])
        );
    }

    public function update(ThemeAdvanceRequest $request)
    {
        foreach ($request->validated() as $key => $code) {
            $setting = Setting::firstOrNew(['key' => $key]);

            $setting->value = $code;

            $setting->save();

            if (
                Str::startsWith($key, 'additional_')
                && !empty($code)
            ) {
                $url = null;
                $filename = $this->settingService->getAdditionalCodeFileName($key);

                $asset = $this->settingService->uploadAdditionalCodeToCloudStorage(
                    $filename,
                    $code,
                    (!App::environment('production') ? config('app.env') : null)
                );

                $url = $asset->fileUrl;

                $this->settingService->saveAdditionalCodeUrl($key, $url);
            }
        }

        $this->settingService->clearStorageTheme();

        $this->generateFlashMessage($this->title.' updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
