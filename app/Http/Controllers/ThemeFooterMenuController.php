<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuItemRequest;
use Illuminate\Http\Request;
use App\Models\{
    Menu,
    MenuItem
};

class ThemeFooterMenuController extends ThemeOptionController
{
    protected $baseRouteName = 'admin.theme.footer';

    public function update(MenuItemRequest $request)
    {
        $inputs = $request->all();

        $menu = Menu::firstOrCreate([
            'locale' => $inputs['locale'],
            'type' => Menu::TYPE_FOOTER,
        ]);

        $menu->syncMenuItems($inputs['menu_items']);

        $this->generateFlashMessage('Menu navigation successfully Saved!');

        return redirect()->route($this->baseRouteName.'.edit');
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();
    }
}
