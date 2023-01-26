<?php

namespace App\Http\Controllers;

use App\Actions\{
    UploadFavicon,
    UploadQrCodeLogo
};
use App\Entities\CloudinaryStorage;
use App\Helpers\HumanReadable;
use App\Http\Requests\ThemeAdvanceRequest;
use App\Services\{
    MediaService,
    MenuService,
    SettingService,
    ThemeService,
};
use Inertia\Inertia;

class ThemeAdvanceController extends CrudController
{
    protected $baseRouteName = 'admin.theme.advance';
    protected $title = 'Advanced';

    private $mediaService;
    private $menuService;
    private $settingService;
    private $themeService;

    public function __construct(
        SettingService $settingService,
        MediaService $mediaService,
        MenuService $menuService,
        ThemeService $themeService
    ) {
        $this->settingService = $settingService;
        $this->mediaService = $mediaService;
        $this->menuService = $menuService;
        $this->themeService = $themeService;
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

                        $this->settingService->saveQrcodeLogo($media->id);
                    }
                break;

                case 'favicon':
                    $oldFaviconMedia = $this->settingService->getFaviconMedia();

                    if ($request->hasFile('favicon')) {
                        $uploadFavicon = new UploadFavicon();

                        $media = $uploadFavicon->handle($inputs, $key);

                        $this->settingService->saveFavicon($media->id);

                    } elseif ($inputs['is_favicon_deleted'] && $oldFaviconMedia) {

                        $this->mediaService->destroy(
                            $oldFaviconMedia,
                            new CloudinaryStorage()
                        );

                        $this->settingService->saveFavicon(null);
                    }
                break;

                default:
                    $this->settingService->saveKey($key, $code);
                break;
            }
        }

        $this->themeService->clearStorageTheme();

        $this->generateFlashMessage($this->title.' updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
