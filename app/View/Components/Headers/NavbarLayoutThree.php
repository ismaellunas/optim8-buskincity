<?php

namespace App\View\Components\Headers;

use App\Services\MenuService;

class NavbarLayoutThree extends BaseNavbarLayout
{
    protected $layoutName = 'navbar-layout-three';

    public $socialMediaMenus = [];

    public function __construct($menus, $currentLanguage, $languageOptions)
    {
        parent::__construct($menus, $currentLanguage, $languageOptions);

        $this->socialMediaMenus = app(MenuService::class)
            ->getSocialMediaMenus();
    }
}