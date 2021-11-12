<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeFooterLayoutRequest as LayoutRequest;
use App\Models\{
    Link,
    Menu,
    Setting,
};
use App\Services\{
    MenuService,
    SettingService,
    LinkService,
    TranslationService,
};
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThemeFooterController extends ThemeOptionController
{
    private $menuService;
    private $settingService;
    private $linkService;
    private $modelMenu = Menu::class;
    private $modelLink = Link::class;

    protected $baseRouteName = 'admin.theme.footer';
    protected $componentName = 'ThemeFooter/';
    protected $title = "Footer";

    public function __construct(
        MenuService $menuService,
        SettingService $settingService,
        LinkService $linkService
    ) {
        $this->menuService = $menuService;
        $this->settingService = $settingService;
        $this->linkService = $linkService;
    }

    public function edit()
    {
        return Inertia::render(
            $this->componentName.'Edit',
            $this->getData([
                'categoryOptions' => $this->menuService->getCategoryOptions(),
                'menu' => $this->modelMenu::footer()->first(),
                'menuItemLastSaved' => $this->menuService->getMenuItemLastSaved("footer"),
                'footerMenus' => $this->menuService->getFooterMenus(
                    TranslationService::getLocales()
                ),
                'links' => $this->linkService->getRecords(),
                'pageOptions' => $this->menuService->getPageOptions(),
                'postOptions' => $this->menuService->getPostOptions(),
                'settings' => $this->settingService->getFooter(),
                'typeOptions' => $this->menuService->getMenuItemTypeOptions(),
            ]),
        );
    }

    public function update(LayoutRequest $request)
    {
        $layout = $request->layout;

        $setting = Setting::firstOrNew(['key' => 'footer_layout']);
        $setting->value = $layout;
        $setting->save();

        $link = new $this->modelLink;
        $link->syncLinks($request->links, $this->modelLink::TYPE_SOCIAL_MEDIA);

        $this->generateFlashMessage('Footer layout updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
