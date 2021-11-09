<?php

namespace App\Http\Controllers;

use App\Models\{
    Menu,
    MenuItem,
    Setting,
};
use App\Services\{
    MenuService,
    SettingService,
};
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThemeFooterController extends ThemeOptionController
{
    private $menuService;
    private $settingService;
    private $modelMenu = Menu::class;
    private $modelMenuItem = MenuItem::class;

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
                'categories' => $this->menuService->getRecordCategories(),
                'menu' => $this->modelMenu::footer()->first(),
                'menuItemLastSaved' => $this->menuService->getMenuItemLastSaved("footer"),
                'menuItems' => $this->menuService->generateMenus(null, "footer"),
                'pages' => $this->menuService->getRecordPages(),
                'posts' => $this->menuService->getRecordPosts(),
                'settings' => $this->settingService->getFooters(),
                'types' => $this->modelMenuItem::TYPES,
            ]),
        );
    }

    public function update(Request $request)
    {
        $layout = $request->layout;

        $setting = Setting::firstOrNew(['key' => 'footer_layout']);
        $setting->value = $layout;
        $setting->save();

        $this->generateFlashMessage('Footer layout updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
