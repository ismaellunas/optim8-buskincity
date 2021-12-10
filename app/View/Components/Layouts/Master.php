<?php

namespace App\View\Components\Layouts;

use App\Services\MenuService;
use App\Services\SettingService;
use App\Services\TranslationService;
use Illuminate\View\Component;

class Master extends Component
{
    public $appCssUrl;
    public $currentLanguage;
    public $languageOptions;
    public $headerLayout;
    public $logoUrl;
    public $menus;
    public $trackingCodeAfterBody;
    public $trackingCodeBeforeBody;
    public $trackingCodeInsideHead;
    public $additionalJavascriptUrl;
    public $additionalCssUrl;

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

        $this->appCssUrl = $settingService->getFrontendCssUrl();
        $this->currentLanguage = $currentLanguage;
        $this->headerLayout = $settingService->getHeaderLayout();
        $this->logoUrl = $settingService->getLogoUrl();
        $this->menus = $menuService->getHeaderMenu($currentLanguage) ?? [];
        $this->trackingCodeAfterBody =  $settingService->getTrackingCodeAfterBody();
        $this->trackingCodeBeforeBody = $settingService->getTrackingCodeBeforeBody();
        $this->trackingCodeInsideHead = $settingService->getTrackingCodeInsideHead();

        $this->additionalJavascriptUrl = $settingService->getAdditionalJavascriptUrl();
        $this->additionalCssUrl = $settingService->getAdditionalCssUrl();

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
        return view('components.layouts.master');
    }
}
