<?php

namespace App\Http\Controllers;

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

    private $menuService;
    private $settingService;
    private $themeService;

    public function __construct(
        SettingService $settingService,
        MenuService $menuService,
        ThemeService $themeService
    ) {
        $this->settingService = $settingService;
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
                    'logoMediaLibrary' => MediaService::logoMediaLibraryInstructions(),
                    'faviconMediaLibrary' => [
                        ...MediaService::defaultMediaLibraryInstructions(),
                        ...[
                            __('Recommended dimension: :dimension.', [
                                'dimension' => config('constants.dimensions.favicon')
                            ]),
                        ]
                    ],
                ],
                'i18n' => $this->translations(),
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

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => $this->title
        ]);

        return redirect()->route($this->baseRouteName.'.edit');
    }

    private function translations(): array
    {
        return [
            ...[
                'save' => __('Save'),
                'homepage' => __('Homepage'),
                'favicon' => __('Favicon'),
                'icon' => __('Icon'),
                'qr_code' => __('QR code public page'),
                'logo' => __('Logo'),
                'additional_code' => __('Additional code'),
                'open_media_library' => __('Open media library'),
                'is_displayed' => __('Is displayed?'),
            ],
            ...MediaService::defaultMediaLibraryTranslations(),
        ];
    }
}
