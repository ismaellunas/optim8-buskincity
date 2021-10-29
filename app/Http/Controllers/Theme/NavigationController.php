<?php

namespace App\Http\Controllers\Theme;

use App\Http\Controllers\CrudController;
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

class NavigationController extends CrudController
{
    protected $baseRouteName = 'admin.theme.header.navigation';
    protected $componentName = 'ThemeHeader/Navigation/';
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
            'categories' => $this->getRecordCategories(),
            'menu' => $this->modelMenu::header()->first(),
            'menuItemLastSaved' => $this->menuService->getMenuItemLastSaved(),
            'menuItems' => $this->menuService->generateMenus(),
            'pages' => $this->getRecordPages(),
            'posts' => $this->getRecordPosts(),
            'types' => $this->modelMenuItem::TYPES,
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
    public function store(MenuItemRequest $request)
    {
        $inputs = $this->generateCustomAttributes($request->all());
        $menuItem = new MenuItem();
        $menuItem->saveFromInputs($inputs);

        $this->generateFlashMessage('Menu item created successfully!');

        return redirect()->route($this->baseRouteName.'.index');
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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

        $this->generateFlashMessage('Menu item updated successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(MenuItem $navigation)
    {
        $navigation->delete();

        $this->generateFlashMessage('User deleted successfully!');

        return redirect()->route($this->baseRouteName.'.index');
    }

    private function getRecordPages()
    {
        $pages = Page::all();
        return $pages->sortBy('title');
    }

    private function getRecordPosts()
    {
        $posts = Post::published()->get();
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
