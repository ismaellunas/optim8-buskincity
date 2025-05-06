<?php

namespace App\View\Components\Headers;

use App\Services\MenuService;
use App\Services\SettingService;
use App\Services\TranslationService;
use Illuminate\View\Component;

class Header extends Component
{
    public $headerLayout;
    public $menus;
    public $currentLanguage;
    public $languageOptions;
    public $logo;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(array $logo)
    {
        $menuService = app(MenuService::class);
        $settingService = app(SettingService::class);
        $currentLanguage = currentLocale();

        $this->logo = $logo;
        $this->currentLanguage = $currentLanguage;
        $this->menus = $menuService->getFrontendUserMenus(request()) ?? [];
        $this->headerLayout = $settingService->getHeaderLayout();
        $this->languageOptions = collect(app(TranslationService::class)->getLocaleOptions())
            ->filter(function ($locale) use ($currentLanguage) {
               return $locale['id'] != $currentLanguage;
            })
            ->all();
    }


    public function headerLayoutName(): string
    {
        $headerLayout = $this->headerLayout;
        switch ($headerLayout) {
            case 1:
                return "headers.navbar-layout-one";
                break;
            case 2:
                return "headers.navbar-layout-two";
                break;
            case 3:
                return "headers.navbar-layout-three";
                break;
            default:
                return "headers.navbar-layout-one";
                break;
        }
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