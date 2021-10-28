<?php

namespace App\Http\Controllers\Theme;

use App\Http\Controllers\CrudController;
use App\Http\Requests\{
    MenuRequest,
    MenuItemRequest,
};
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

class NavigationController extends CrudController
{
    protected $baseRouteName = 'admin.theme.header.navigation';
    protected $componentName = 'Theme/Header/Navigation/';
    protected $modelMenu = Menu::class;
    protected $modelMenuItem = MenuItem::class;
    private $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->authorizeResource(Menu::class, 'menu');

        $this->menuService = $menuService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render($this->componentName.'Index', [
            'baseRouteName' => $this->baseRouteName,
            'lastSaved' => $this->menuService->getLastSaved(),
            'records' => $this->menuService->getRecords(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MenuRequest $request)
    {
        $menu = new Menu();
        $menu->saveFromInputs($request->validated());

        $this->updateActiveMenu($menu->id);
        $this->generateFlashMessage('Menu created successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    public function storeMenuItem(MenuItemRequest $request)
    {
        $inputs = $this->generateCustomAttributes($request->validated());
        $menuItem = new MenuItem();
        $menuItem->saveFromInputs($inputs);

        $this->generateFlashMessage('Menu item created successfully!');

        return redirect()->route($this->baseRouteName.'.edit', $menuItem->menu_id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Menu $navigation)
    {
        $menu = $navigation;
        return Inertia::render($this->componentName.'Edit', [
            'baseRouteName' => $this->baseRouteName,
            'menu' => $menu,
            'menuItems' => $menu->menuItems,
            'types' => $this->modelMenuItem::TYPES,
            'categories' => $this->getRecordCategories(),
            'pages' => $this->getRecordPages(),
            'posts' => $this->getRecordPosts(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function updateMenuItem(
        MenuItemRequest $request,
        MenuItem $menuItem
    ) {
        $inputs = $this->generateCustomAttributes($request->validated());

        $menuItem->saveFromInputs($inputs);

        $menuItem->syncTranslations(array_keys($inputs));

        $this->generateFlashMessage('Menu item updated successfully!');

        return redirect()->route($this->baseRouteName.'.edit', $menuItem->menu_id);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Menu $navigation)
    {
        $menu = $navigation;
        $menu->delete();

        $this->generateFlashMessage('User deleted successfully!');

        return redirect()->back();
    }

    public function deleteMenuItem(MenuItem $menuItem)
    {
        $menuItem->delete();

        $this->generateFlashMessage('User deleted successfully!');

        return redirect()->back();
    }

    private function updateActiveMenu($menuId)
    {
        $menus = $this->modelMenu::whereNotIn('id', [$menuId])->get();
        foreach ($menus as $menu) {
            $menu->is_active = false;
            $menu->save();
        }
    }

    private function getRecordPages()
    {
        $pages = Page::all();
        return $pages->sortBy('title');
    }

    private function getRecordPosts()
    {
        $posts = Post::all();
        return $posts->sortBy('title');
    }

    private function getRecordCategories()
    {
        $categories = Category::all();
        return $categories->sortBy('name');
    }

    private function generateCustomAttributes($inputs)
    {
        $lastMenuItem = $this->modelMenuItem::orderBy('order', 'DESC')
            ->where('menu_id', $inputs['menu_id'])
            ->first();

        if ($lastMenuItem) {
            $inputs['order'] = $lastMenuItem->order + 1;
        } else {
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
