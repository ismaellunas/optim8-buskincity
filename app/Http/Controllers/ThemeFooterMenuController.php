<?php

namespace App\Http\Controllers;

use App\Entities\Caches\SettingCache;
use App\Http\Requests\MenuRequest;
use App\Models\Menu;

class ThemeFooterMenuController extends ThemeOptionController
{
    protected $baseRouteName = 'admin.theme.footer';

    public function update(MenuRequest $request)
    {
        $inputs = $request->all();

        $menu = Menu::firstOrCreate([
            'locale' => $inputs['locale'],
            'type' => Menu::TYPE_FOOTER,
        ]);

        $menu->syncMenuItems($inputs['menu_items']);

        app(SettingCache::class)->flush();

        $this->generateFlashMessage('Menu navigation successfully Saved!');

        return redirect()->route($this->baseRouteName.'.edit');
    }
}
