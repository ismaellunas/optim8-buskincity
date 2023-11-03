<?php

namespace App\View\Components\Headers;

class NavbarLayoutTwo extends BaseNavbarLayout
{
    protected $layoutName = 'navbar-layout-two';

    public function __construct($menus, $currentLanguage, $languageOptions)
    {
        parent::__construct($menus, $currentLanguage, $languageOptions);

        $this->splitMenus();
    }

    private function splitMenus(): void
    {
        $half = ceil(count($this->menus['nav']) / 2);

        $this->menus['splited'] = collect($this->menus['nav'])
            ->chunk($half)
            ->map(fn ($menu) => $menu->values()->all())
            ->all();
    }
}