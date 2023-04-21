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
use Inertia\Inertia;
use App\Entities\Menus\Options\SegmentOption;

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
        return Inertia::render(
            $this->componentName.'Edit',
            $this->getData([
                'menu' => $this->modelMenu::footer()->first(),
                'footerMenus' => $this->menuService->getFooterMenus(
                    app(TranslationService::class)->getLocales()
                ),
                'settings' => $this->settingService->getFooter(),
                'socialMediaMenus' => $this->menuService->getSocialMediaMenus(),
                'typeOptions' => $this->menuService->getMenuItemTypeOptions(),
                'menuOptions' => $this->menuService->getMenuOptions(),
                'typeSegment' => (new SegmentOption)->getKey(),
                'i18n' => $this->translations(),
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
            'locale' => defaultLocale(),
            'type' => Menu::TYPE_SOCIAL_MEDIA,
        ]);

        $menu->syncSocialMedia($request->social_media_menus);

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Footer Layout')
        ]);

        return redirect()->route($this->baseRouteName.'.edit');
    }

    public function apiValidateSocialMedia(SocialMediaRequest $request)
    {
        return [
            'passed' => true
        ];
    }

    private function translations(): array
    {
        return [
            'layout' => __('Layout'),
            'navigation' => __('Navigation'),
            'footer_layout' => __('Footer layout'),
            'standard' => __('Standard'),
            'social_media' => __('Social media'),
            'social_media_items' => __('Social media items'),
            'add_social_media' => __('Add :resource', ['resource' => __('Social media item')]),
            'edit_social_media' => __('Edit :resource', ['resource' => __('Social media item')]),
            'icon' => __('Icon'),
            'link' => __('Link'),
            'save' => __('Save'),
            'menu_items' => __('Menu items'),
            'add_menu_item' => __('Add :resource', ['resource' => __('Menu item')]),
            'edit_menu_item' => __('Edit :resource', ['resource' => __('Menu item')]),
            'duplicate_menu' => __('Duplicate :resource', ['resource' => __('Menu')]),
            'to' => __('To'),
            'title' => __('Title'),
            'type' => __('Type'),
            'url' => __('Url'),
            'menu' => __('Menu'),
            'open_link' => __('Open link in a new tab'),
            'cancel' => __('Cancel'),
            'create' => __('Create'),
            'update' => __('Update'),
            'duplicate' => __('Duplicate'),
            'add_new_segment' => __('Add :resource', ['resource' => __('New segment')]),
        ];
    }
}
