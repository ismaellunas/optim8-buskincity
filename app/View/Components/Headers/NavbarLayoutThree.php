<?php

namespace App\View\Components\Headers;

use App\Services\MenuService;

class NavbarLayoutThree extends BaseNavbarLayout
{
    public $layoutName = 'navbar-layout-three';
    public $socialMediaMenus;

    public function __construct($menus, $currentLanguage, $logoUrl, $languageOptions)
    {
        parent::__construct($menus, $currentLanguage, $logoUrl, $languageOptions);
        $menuService = app(MenuService::class);
        $this->socialMediaMenus = $menuService->getSocialMediaMenus();

    }
}