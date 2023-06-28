<?php

namespace App\View\Components;

use App\Services\MenuService;
use Illuminate\View\Component;

class Footer extends Component
{
    public $logo;
    public $menus;
    public $socialMediaMenus;

    public function __construct(array $logo)
    {
        $menuService = app(MenuService::class);

        $this->logo = $logo;
        $this->menus = $menuService->getFrontendUserFooterMenus(request()) ?? [];
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
