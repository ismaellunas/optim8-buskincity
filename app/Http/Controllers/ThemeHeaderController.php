<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuItemRequest;
use App\Http\Requests\ThemeHeaderLogoRequest as LogoRequest;
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

class ThemeHeaderController extends CrudController
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
                'types' => $this->modelMenuItem::TYPES,
            ]),
        );
    }

    // Header handle
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

    // Navigation handle
    public function store(MenuItemRequest $request)
    {
        $inputs = $this->generateCustomValues($request->all());

        $menuItem = new MenuItem();
        $menuItem->saveFromInputs($inputs);

        $this->generateFlashMessage('Menu item created successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }

    public function update(
        MenuItemRequest $request,
        MenuItem $menuItem
    ) {
        $inputs = $this->generateCustomValues($request->all());

        $menuItem->saveFromInputs($inputs);

        $this->generateFlashMessage('Menu item updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }

    public function updateFormat(Request $request)
    {
        $menuItems = new $this->modelMenuItem;

        $menuItems->updateFormatMenuItems($request->all());

        $this->generateFlashMessage('Menu navigation successfully Saved!');

        return redirect()->route($this->baseRouteName.'.edit');
    }

    public function destroy(MenuItem $menuItem)
    {
        $this->updateParentId($menuItem);

        $menuItem->delete();

        $this->generateFlashMessage('Menu navigation deleted successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }

    public function duplicateMenu(MenuItemRequest $request, $type)
    {
        $inputs = $request->all();

        if ($type != 1) {
            $inputs['order'] = (int)$inputs['order'] + 1;
        }

        $menuItem = new MenuItem();
        $menuItem->updateOrderMenuItem($inputs);
        $menuItem->saveFromInputs($inputs);

        $this->generateFlashMessage('Menu item duplicate successfully!');

        return redirect()->route($this->baseRouteName.'.edit');
    }

    private function updateParentId($menuItem)
    {
        $childs = $this->modelMenuItem::where('parent_id', $menuItem->id)->get();

        foreach ($childs as $child) {
            $child->parent_id = $menuItem->parent_id;
            $child->save();
        }
    }

    private function generateCustomValues($inputs)
    {
        $lastMenuItem = $this->modelMenuItem::orderBy('order', 'DESC')
            ->where('locale', $inputs['locale'])
            ->where('menu_id', $inputs['menu_id'])
            ->first();

        if ($lastMenuItem && $inputs['id'] === null) {
            $inputs['order'] = $lastMenuItem->order + 1;
        } else if (!$lastMenuItem && $inputs['id'] === null) {
            $inputs['order'] = 1;
        }

        if ($inputs['type'] == $this->modelMenuItem::TYPE_URL) {
            $inputs['page_id'] = null;
            $inputs['post_id'] = null;
            $inputs['category_id'] = null;
        }

        if ($inputs['type'] == $this->modelMenuItem::TYPE_PAGE) {
            $inputs['url'] = null;
            $inputs['post_id'] = null;
            $inputs['category_id'] = null;
        }

        if ($inputs['type'] == $this->modelMenuItem::TYPE_POST) {
            $inputs['url'] = null;
            $inputs['page_id'] = null;
            $inputs['category_id'] = null;
        }

        if ($inputs['type'] == $this->modelMenuItem::TYPE_CATEGORY) {
            $inputs['url'] = null;
            $inputs['page_id'] = null;
            $inputs['post_id'] = null;
        }

        return $inputs;
    }
}
