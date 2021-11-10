<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThemeHeaderLayoutRequest as LayoutRequest;
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
                'categoryOptions' => $this->menuService->getCategoryOptions(),
                'menu' => $this->modelMenu::header()->first(),
                'menuItemLastSaved' => $this->menuService->getMenuItemLastSaved("header"),
                'menuItems' => $this->menuService->generateMenus(),
                'pageOptions' => $this->menuService->getPageOptions(),
                'postOptions' => $this->menuService->getPostOptions(),
                'settings' => $this->settingService->getHeader(),
                'typeOptions' => $this->menuService->getMenuItemTypeOptions(),
            ]),
        );
    }

    public function update(LayoutRequest $request)
    {
        $inputs = $request->all();

        $setting = Setting::firstOrNew(['key' => 'header_layout']);
        $setting->value = $inputs['layout'];
        $setting->save();

        if ($request->hasFile('logo.file')) {
            $upload = $this->settingService->uploadLogoToCloudStorage($inputs['logo']);

            $setting = Setting::firstOrNew(['key' => 'header_logo_url']);
            $setting->display_name = $upload->fileName;
            $setting->value = $upload->fileUrl;
            $setting->save();
        }

        $this->generateFlashMessage('Header layout updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
