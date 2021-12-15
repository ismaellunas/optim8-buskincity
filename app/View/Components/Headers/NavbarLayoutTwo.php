<?php

namespace App\View\Components\Headers;

class NavbarLayoutTwo extends BaseNavbarLayout
{
    public $menuChunks;
    public $layoutName = 'navbar-layout-two';

    public function __construct($menus, $currentLanguage, $logoUrl, $languageOptions)
    {
        parent::__construct($menus, $currentLanguage, $logoUrl, $languageOptions);
        $this->menuChunks = $this->splitMenu();
    }

    private function splitMenu()
    {
        $half = ceil(count($this->menus) / 2);
        return collect($this->menus)->chunk($half);
    }
}