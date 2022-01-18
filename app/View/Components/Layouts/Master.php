<?php

namespace App\View\Components\Layouts;

use App\Services\SettingService;
use Illuminate\View\Component;

class Master extends Component
{
    public $appCssUrl;
    public $logoUrl;
    public $trackingCodeAfterBody;
    public $trackingCodeBeforeBody;
    public $trackingCodeInsideHead;
    public $additionalJavascript;
    public $additionalCss;
    public $baseGoogleUrlFont;
    public $mainTextFont;
    public $headingsFont;
    public $buttonsFont;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $settingService = app(SettingService::class);

        $this->appCssUrl = $settingService->getFrontendCssUrl();
        $this->logoUrl = $settingService->getLogoUrl();
        $this->trackingCodeAfterBody =  $settingService->getTrackingCodeAfterBody();
        $this->trackingCodeBeforeBody = $settingService->getTrackingCodeBeforeBody();
        $this->trackingCodeInsideHead = $settingService->getTrackingCodeInsideHead();
        $this->additionalJavascript = $settingService->getAdditionalJavascript();
        $this->additionalCss = $settingService->getAdditionalCss();
        $this->mainTextFont = $settingService->getFont('main_text_font');
        $this->headingsFont = $settingService->getFont('headings_font');
        $this->buttonsFont = $settingService->getFont('buttons_font');
        $this->baseGoogleUrlFont = 'https://fonts.googleapis.com/css2';
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
