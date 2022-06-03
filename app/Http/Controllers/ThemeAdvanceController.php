<?php

namespace App\Http\Controllers;

use App\Actions\{
    UploadFavicon,
    UploadQrCodeLogo
};
use App\Helpers\HumanReadable;
use App\Http\Requests\ThemeAdvanceRequest;
use App\Models\Setting;
use App\Services\{
    MenuService,
    SettingService,
};
use Inertia\Inertia;

class ThemeAdvanceController extends ThemeOptionController
{
    protected $baseRouteName = 'admin.theme.advance';
    protected $title = 'Advanced';

    private $settingService;
    private $menuService;

    public function __construct(
        SettingService $settingService,
        MenuService $menuService
    ) {
        $this->settingService = $settingService;
        $this->menuService = $menuService;
    }

    public function edit()
    {
        $pageOptions = $this->menuService->getPageOptions();
        $pageOptions[] = [
            'id' => "",
            'value' => 'Default',
            'locales' => null,
        ];

        return Inertia::render(
            'ThemeAdvance',
            $this->getData([
                'additionalCodes' => $this->settingService->getAdditionalCodes(),
                'faviconUrl' => $this->settingService->getFaviconUrl(),
                'homePageId' => $this->settingService->getHomePage(),
                'pageOptions' => $pageOptions,
                'qrCodePublicPageIsDisplayed' => $this->settingService->qrCodePublicPageIsDisplayed(),
                'qrCodePublicPageLogo' => $this->settingService->qrCodePublicPageLogo(),
                'trackingCodes' => $this->settingService->getTrackingCodes(),
                'instructions' => [
                    'qrcode' => [
                        __('Accepted file extensions: :extensions.', [
                            'extensions' => implode(', ', config('constants.extensions.image'))
                        ]),
                        __('Max file size: :filesize.', [
                            'filesize' => HumanReadable::bytesToHuman(
                                (50 * config('constants.one_megabyte')) * 1024
                            )
                        ]),
                    ],
                    'favicon' => [
                        __('Accepted file extensions: :extensions.', [
                            'extensions' => implode(', ', config('constants.extensions.image'))
                        ]),
                        __('Max file size: :filesize.', [
                            'filesize' => HumanReadable::bytesToHuman(
                                (1 * config('constants.one_megabyte')) * 1024
                            )
                        ]),
                    ]
                ]
            ])
        );
    }

    public function update(ThemeAdvanceRequest $request)
    {
        $inputs = $request->validated();
        foreach ($inputs as $key => $code) {

            switch ($key) {
                case 'qrcode_public_page_logo':
                    if ($request->hasFile('qrcode_public_page_logo')) {
                        $uploadQrCodeLogo = new UploadQrCodeLogo();

                        $media = $uploadQrCodeLogo->handle($inputs, $key);

                        $this->syncSetting(
                            'qrcode_public_page_logo_media_id',
                            $media->id
                        );
                    }
                break;

                case 'favicon':
                    if ($request->hasFile('favicon')) {
                        $uploadFavicon = new UploadFavicon();

                        $media = $uploadFavicon->handle($inputs, $key);

                        $this->syncSetting(
                            'favicon_media_id',
                            $media->id
                        );
                    }
                break;

                default:
                    $this->syncSetting($key, $code);
                break;
            }
        }

        $this->settingService->clearStorageTheme();

        $this->generateFlashMessage($this->title.' updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }

    private function syncSetting($key, $value): void
    {
        $setting = Setting::firstOrNew(['key' => $key]);

        $setting->value = $value;

        $setting->save();
    }
}
