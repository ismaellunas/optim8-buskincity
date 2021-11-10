<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuItemRequest;
use Illuminate\Http\Request;
use App\Models\{
    Menu,
    MenuItem
};

class ThemeHeaderMenuController extends ThemeOptionController
{
    private $model = MenuItem::class;

    protected $baseRouteName = 'admin.theme.header';

    public function update(Request $request)
    {
        $inputs = $request->all();

        $menuItems = new $this->model;

        $menu = Menu::firstOrCreate([
            'locale' => $inputs['locale'],
            'type' => Menu::TYPE_HEADER,
        ]);

        $menuItems->updateMenuItems($inputs['menuItems'], $menu->id);

        $this->generateFlashMessage('Menu navigation successfully Saved!');

        return redirect()->route($this->baseRouteName.'.edit');
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();
    }
}
