<?php

namespace App\Http\Controllers;

use App\Http\Requests\{
    SocialMediaRequest,
    ThemeFooterLayoutRequest
};
use App\Models\{
    Menu,
    Setting,
};
use App\Services\{
    MenuService,
    SettingService,
    TranslationService,
};
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThemeFooterController extends ThemeOptionController
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
        return Inertia::render(
            $this->componentName.'Edit',
            $this->getData([
                'categoryOptions' => $this->menuService->getCategoryOptions(),
                'menu' => $this->modelMenu::footer()->first(),
                'footerMenus' => $this->menuService->getFooterMenus(
                    TranslationService::getLocales()
                ),
                'pageOptions' => $this->menuService->getPageOptions(),
                'postOptions' => $this->menuService->getPostOptions(),
                'settings' => $this->settingService->getFooter(),
                'socialMediaMenus' => $this->menuService->getSocialMediaMenus(),
                'typeOptions' => $this->menuService->getMenuItemTypeOptions(),
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
