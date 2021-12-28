<?php

namespace App\View\Components;

use App\Services\{
    MenuService,
    TranslationService,
};
use Illuminate\View\Component;

class Footer extends Component
{
    public $logoUrl;
    public $menus;
    public $socialMediaMenus;

    public function __construct($logoUrl)
    {
        $menuService = app(MenuService::class);
        $currentLanguage = TranslationService::currentLanguage();

        $this->logoUrl = $logoUrl !== "" ? $logoUrl : null;
        $this->menus = $menuService->getFooterMenu($currentLanguage);
        $this->socialMediaMenus = $menuService->getSocialMediaMenus();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.footer');
    }
}
