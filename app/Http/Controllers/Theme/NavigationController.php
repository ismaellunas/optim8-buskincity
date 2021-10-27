<?php

namespace App\Http\Controllers\Theme;

use App\Http\Controllers\CrudController;
use App\Http\Requests\MenuRequest;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NavigationController extends CrudController
{
    protected $baseRouteName = 'admin.theme.header.navigation';
    protected $componentName = 'Theme/Header/Navigation/';
    protected $model = Menu::class;
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

    private function updateActiveMenu($menuId)
    {
        $menus = $this->model::whereNotIn('id', [$menuId])->get();
        foreach ($menus as $menu) {
            $menu->is_active = false;
            $menu->save();
        }
    }
}
