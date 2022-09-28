<?php

namespace App\Http\Controllers;

use App\Http\Requests\{
    SocialMediaRequest,
    ThemeFooterLayoutRequest
};
use App\Models\{
    Menu,
    MenuItem,
    Setting,
};
use App\Services\{
    MenuService,
    ModuleService,
    SettingService,
    TranslationService,
};
use Inertia\Inertia;
use Modules\Space\Services\PageService as SpacePageService;

class ThemeFooterController extends CrudController
{
    private $menuService;
    private $settingService;
    private $modelMenu = Menu::class;

    protected $baseRouteName = 'admin.theme.footer';
    protected $componentName = 'ThemeFooter/';
    protected $title = "Footer";

    public function __construct(
        MenuService $menuService,
        SettingService $settingService
    ) {
        $this->menuService = $menuService;
        $this->settingService = $settingService;
    }

    public function edit()
    {
        $additionalPageOptions = [];
        $isModuleSpaceActive = app(ModuleService::class)->isModuleActive('space');

        if ($isModuleSpaceActive) {
            $additionalPageOptions = app(SpacePageService::class)->getPageOptions();
        }

        return Inertia::render(
            $this->componentName.'Edit',
            $this->getData([
                'categoryOptions' => $this->menuService->getCategoryOptions(),
                'menu' => $this->modelMenu::footer()->first(),
                'footerMenus' => $this->menuService->getFooterMenus(
                    app(TranslationService::class)->getLocales()
                ),
                'pageOptions' => array_merge(
                    $this->menuService->getPageOptions(),
                    $additionalPageOptions
                ),
                'postOptions' => $this->menuService->getPostOptions(),
                'settings' => $this->settingService->getFooter(),
                'socialMediaMenus' => $this->menuService->getSocialMediaMenus(),
                'typeOptions' => $this->menuService->getMenuItemTypeOptions(),
                'typeSegment' => MenuItem::TYPE_SEGMENT,
            ]),
        );
    }

    public function update(ThemeFooterLayoutRequest $request)
    {
        $layout = $request->layout;

        $setting = Setting::firstOrNew(['key' => 'footer_layout']);
        $setting->value = $layout;
        $setting->save();

        $menu = Menu::firstOrCreate([
            'type' => Menu::TYPE_SOCIAL_MEDIA,
        ], [
            'locale' => TranslationService::getDefaultLocale(),
            'type' => Menu::TYPE_SOCIAL_MEDIA,
        ]);

        $menu->syncSocialMedia($request->social_media_menus);

        $this->generateFlashMessage('Footer layout updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }

    public function apiValidateSocialMedia(SocialMediaRequest $request)
    {
        return [
            'passed' => true
        ];
    }
}
