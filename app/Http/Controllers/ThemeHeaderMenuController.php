<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuItemRequest;
use App\Models\MenuItem;
use App\Services\MenuService;

class ThemeHeaderMenuController extends ThemeOptionController
{
    private $model = MenuItem::class;

    protected $baseRouteName = 'admin.theme.header';

    public function update(MenuItemRequest $request)
    {
        $inputs = $request->all();

        $menuItems = new $this->model;
        $menuItems->updateMenuItems($inputs);

        $this->generateFlashMessage('Menu navigation successfully Saved!');

        return redirect()->route($this->baseRouteName.'.edit');
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();
    }
}
