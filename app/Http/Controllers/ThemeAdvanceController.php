<?php

namespace App\Http\Controllers;

use App\Entities\CloudinaryStorage;
use App\Helpers\HumanReadable;
use App\Http\Requests\ThemeAdvanceRequest;
use App\Models\Setting;
use App\Services\{
    MediaService,
    MenuService,
    SettingService,
};
use Illuminate\Support\{
    Facades\App,
    Str
};
use Inertia\Inertia;

class ThemeAdvanceController extends ThemeOptionController
{
    protected $baseRouteName = 'admin.theme.advance';
    protected $title = 'Advanced';

    private $settingService;
    private $mediaService;
    private $menuService;

    public function __construct(
        SettingService $settingService,
        MediaService $mediaService,
        MenuService $menuService
    ) {
        $this->settingService = $settingService;
        $this->mediaService = $mediaService;
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
                'trackingCodes' => $this->settingService->getTrackingCodes(),
                'additionalCodes' => $this->settingService->getAdditionalCodes(),
                'homePageId' => $this->settingService->getHomePage(),
                'qrCodePublicPageIsDisplayed' => $this->settingService->qrCodePublicPageIsDisplayed(),
                'qrCodePublicPageLogo' => $this->settingService->qrCodePublicPageLogo(),
                'pageOptions' => $pageOptions,
                'qrCodeLogoInstructions' => [
                    __('Accepted file extensions: :extensions.', [
                        'extensions' => implode(', ', config('constants.extensions.image'))
                    ]),
                    __('Max file size: :filesize.', [
                        'filesize' => HumanReadable::bytesToHuman(
                            50 * config('constants.one_megabyte')
                        )
                    ]),
                ],
            ])
        );
    }

    public function update(ThemeAdvanceRequest $request)
    {
        $inputs = $request->validated();
        foreach ($inputs as $key => $code) {

            if ($key == 'qrcode_public_page_logo') {

                if ($request->hasFile('qrcode_public_page_logo')) {
                    $media = $this->mediaService->uploadSetting(
                        $inputs['qrcode_public_page_logo'],
                        Str::random(10),
                        new CloudinaryStorage(),
                        (!App::environment('production') ? config('app.env') : null)
                    );

                    $existingMedia = $this->settingService->getQrCodePublicPageLogoMedia();

                    $setting = Setting::firstOrNew([
                        'key' => 'qrcode_public_page_logo_media_id'
                    ]);
                    $setting->value = $media->id;
                    $setting->save();

                    if ($existingMedia) {
                        $this->mediaService->destroy(
                            $existingMedia,
                            new CloudinaryStorage()
                        );
                    }
                }

            } else {
                $setting = Setting::firstOrNew(['key' => $key]);

                $setting->value = $code;

                $setting->save();
            }
        }

        $this->settingService->clearStorageTheme();

        $this->generateFlashMessage($this->title.' updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
