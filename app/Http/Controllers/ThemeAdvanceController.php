<?php

namespace App\Http\Controllers;

use App\Entities\CloudinaryStorage;
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
        $user = auth()->user();
        $pageOptions = $this->menuService->getPageOptions();
        $pageOptions[] = [
            'id' => "",
            'value' => 'Default',
            'locales' => null,
        ];

        $faviconMedia = $this->settingService->getFaviconMedia();
        $qrCodeMedia = $this->settingService->getQrCodePublicPageLogoMedia();

        return Inertia::render(
            'ThemeAdvance',
            $this->getData([
                'additionalCodes' => $this->settingService->getAdditionalCodes(),
                'can' => [
                    'media' => [
                        'read' => $user->can('media.read'),
                        'add' => $user->can('media.add'),
                    ]
                ],
                'faviconMedia' => $faviconMedia,
                'homePageId' => $this->settingService->getHomePage(),
                'pageOptions' => $pageOptions,
                'qrCodeMedia' => $qrCodeMedia,
                'qrCodePublicPageIsDisplayed' => $this->settingService->qrCodePublicPageIsDisplayed(),
                'trackingCodes' => $this->settingService->getTrackingCodes(),
                'instructions' => [
                    'mediaLibrary' => MediaService::defaultMediaLibraryInstructions(),
                ],
            ])
        );
    }

    public function update(ThemeAdvanceRequest $request)
    {
        $inputs = $request->validated();


        foreach ($inputs as $key => $code) {

            switch ($key) {
                case 'qrcode_public_page_logo':
                    $this->settingService->saveQrcodeLogo($inputs[$key]);
                break;

                case 'favicon':
                    $this->settingService->saveFavicon($inputs[$key]);
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
