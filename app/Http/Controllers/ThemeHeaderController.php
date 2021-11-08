<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuItemRequest;
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

class ThemeHeaderController extends ThemeOptionController
{
    private $menuService;
    private $settingService;
    private $modelMenu = Menu::class;
    private $modelMenuItem = MenuItem::class;

    protected $baseRouteName = 'admin.theme.header';
    protected $componentName = 'ThemeHeader/';
    protected $title = "Header";

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
                'menu' => $this->modelMenu::header()->first(),
                'menuItemLastSaved' => $this->menuService->getMenuItemLastSaved("header"),
                'menuItems' => $this->menuService->generateMenus(),
                'pages' => $this->menuService->getRecordPages(),
                'posts' => $this->menuService->getRecordPosts(),
                'settings' => $this->settingService->getHeader(),
                'types' => $this->modelMenuItem::TYPES,
            ]),
        );
    }

    public function updateLayout(Request $request)
    {
        $layout = $request->layout;

        $setting = Setting::firstOrNew(['key' => 'header_layout']);
        $setting->value = $layout;
        $setting->save();

        $this->generateFlashMessage('Header layout updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }

    public function updateLogo(LogoRequest $request)
    {
        $inputs = $request->all();

        $upload = $this->settingService->uploadLogoToCloudStorage($inputs);

        $setting = Setting::firstOrNew(['key' => 'header_logo_url']);
        $setting->display_name = $upload->fileName;
        $setting->value = $upload->fileUrl;
        $setting->save();

        $this->generateFlashMessage('Header logo upload successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
