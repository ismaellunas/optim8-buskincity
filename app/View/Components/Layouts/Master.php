<?php

namespace App\View\Components\Layouts;

use App\Services\SettingService;
use App\Services\TranslationService;
use Illuminate\View\Component;

class Master extends Component
{
    public $appCssUrl;
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
        $settingService = app(SettingService::class);

        $this->appCssUrl = $settingService->getFrontendCssUrl();

        $this->trackingCodeAfterBody =  $settingService->getTrackingCodeAfterBody();
        $this->trackingCodeBeforeBody = $settingService->getTrackingCodeBeforeBody();
        $this->trackingCodeInsideHead = $settingService->getTrackingCodeInsideHead();

        $this->additionalJavascriptUrl = $settingService->getAdditionalJavascriptUrl();
        $this->additionalCssUrl = $settingService->getAdditionalCssUrl();
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
