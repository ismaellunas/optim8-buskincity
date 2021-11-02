<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuItemRequest;
use App\Models\{
    Menu,
    MenuItem,
    Page,
    Post,
    Category
};
use App\Services\MenuService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThemeNavigationController extends CrudController
{
    protected $baseRouteName = 'admin.theme.header.navigation';
    protected $componentName = 'ThemeHeader/Navigation/';
    protected $modelMenu = Menu::class;
    protected $modelMenuItem = MenuItem::class;
    protected $title = "Header";
    private $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function index()
    {
        return Inertia::render(
            $this->componentName.'Index',
            $this->getData([
                'categories' => $this->menuService->getRecordCategories(),
                'menu' => $this->modelMenu::header()->first(),
                'menuItemLastSaved' => $this->menuService->getMenuItemLastSaved(),
                'menuItems' => $this->menuService->generateMenus(),
                'pages' => $this->menuService->getRecordPages(),
                'posts' => $this->menuService->getRecordPosts(),
                'types' => $this->modelMenuItem::TYPES,
            ]),
        );
    }

    public function store(MenuItemRequest $request)
    {
        $inputs = $this->generateCustomAttributes($request->all());

        $menuItem = new MenuItem();
        $menuItem->saveFromInputs($inputs);

        $this->generateFlashMessage('Menu item created successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    public function update(
        MenuItemRequest $request,
        MenuItem $navigation
    ) {
        $inputs = $this->generateCustomAttributes($request->all());

        $navigation->saveFromInputs($inputs);

        $navigation->syncTranslations(array_keys($inputs));

        $this->generateFlashMessage('Menu item updated successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    public function updateFormat(Request $request)
    {
        $menuItems = new $this->modelMenuItem;

        $menuItems->updateFormatMenuItems($request->all());

        $this->generateFlashMessage('Menu Navigation Successfully Saved!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    public function destroy(MenuItem $navigation)
    {
        $this->updateParentId($navigation);

        $navigation->delete();

        $this->generateFlashMessage('User deleted successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    private function updateParentId($menuItem)
    {
        $childs = $this->modelMenuItem::where('parent_id', $menuItem->id)->get();

        foreach ($childs as $child) {
            $child->parent_id = $menuItem->parent_id;
            $child->save();
        }
    }

    private function generateCustomAttributes($inputs)
    {
        $lastMenuItem = $this->modelMenuItem::orderBy('order', 'DESC')
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
