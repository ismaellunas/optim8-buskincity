<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenuItemRequest;
use App\Http\Requests\MenuRequest;
use App\Models\Menu;

class ThemeHeaderMenuController extends CrudController
{
    protected $baseRouteName = 'admin.theme.header';

    public function update(MenuRequest $request)
    {
        $inputs = $request->all();

        $menu = Menu::firstOrCreate([
            'locale' => $inputs['locale'],
            'type' => Menu::TYPE_HEADER,
        ]);

        $menu->syncMenuItems($inputs['menu_items']);

        $this->generateFlashMessage('The :resource was updated!', [
            'resource' => __('Menu Navigation')
        ]);

        return redirect()->route($this->baseRouteName.'.edit');
    }

    public function apiValidateMenuItem(MenuItemRequest $request)
    {
        return [
            'passed' => true
        ];
    }
}
