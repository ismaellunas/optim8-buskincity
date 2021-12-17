<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use App\Traits\FlashNotifiable;
use Illuminate\Support\Facades\App;

abstract class ThemeOptionController
{
    use FlashNotifiable;

    protected function getData(array $additionalData = []): array
    {
        return array_merge(
            [
                'baseRouteName' => $this->baseRouteName,
                'title' => $this->title,
            ],
            $additionalData
        );
    }

    protected function generateNewStyleProcess(SettingService $settingService)
    {
        $settingService->generateVariablesSass();

        $settingService->generateThemeCss();

        $asset = $settingService->uploadThemeCssToCloudStorage(
            !App::environment('production')
            ? config('app.env')
            : null
        );

        $settingService->saveCssUrl($asset->fileUrl);

        $settingService->clearStorageTheme();
    }
}
