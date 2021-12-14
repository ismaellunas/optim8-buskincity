<?php

namespace App\View\Components\Headers;

use App\Services\MenuService;
use App\Services\SettingService;
use App\Services\TranslationService;
use Illuminate\View\Component;

class Header extends Component
{
    public $headerLayoutName;
    public $logoUrl;
    public $menus;
    public $currentLanguage;
    public $languageOptions;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $menuService = app(MenuService::class);
        $settingService = app(SettingService::class);
        $currentLanguage = TranslationService::currentLanguage();
        $this->currentLanguage = $currentLanguage;
        $this->menus = $menuService->getHeaderMenu($currentLanguage) ?? [];
        $this->headerLayoutName = $settingService->getHeaderLayoutName();
        $this->logoUrl = $settingService->getLogoUrl();

        $this->languageOptions = collect(TranslationService::getLocaleOptions())
            ->filter(function ($locale) use ($currentLanguage) {
               return $locale['id'] != $currentLanguage;
            })
            ->all();

    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.headers.header');
    }
}
