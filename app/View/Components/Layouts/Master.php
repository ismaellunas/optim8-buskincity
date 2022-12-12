<?php

namespace App\View\Components\Layouts;

use App\Services\SettingService;
use Illuminate\View\Component;

class Master extends Component
{
    public $appCssUrl;
    public $faviconUrl;
    public $logoUrl;
    public $trackingCodeAfterBody;
    public $trackingCodeBeforeBody;
    public $trackingCodeInsideHead;
    public $additionalJavascript;
    public $additionalCss;
    public $fontUrls;
    public $hasHeader = true;
    public $hasFooter = true;
    public $bodyClasses = [];
    public $bodyStyles = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        bool $hasHeader = true,
        bool $hasFooter = true,
        array $bodyClasses = [],
        mixed $bodyStyles = null,
    ) {
        $settingService = app(SettingService::class);

        $this->appCssUrl = $settingService->getFrontendCssUrl();
        $this->faviconUrl = $settingService->getFaviconUrl();
        $this->logoUrl = $settingService->getLogoOrDefaultUrl();
        $this->trackingCodeAfterBody =  $settingService->getTrackingCodeAfterBody();
        $this->trackingCodeBeforeBody = $settingService->getTrackingCodeBeforeBody();
        $this->trackingCodeInsideHead = $settingService->getTrackingCodeInsideHead();
        $this->additionalJavascript = $settingService->getAdditionalJavascript();
        $this->additionalCss = $settingService->getAdditionalCss();
        $this->fontUrls = $settingService->getFontUrls();
        $this->hasHeader = $hasHeader;
        $this->hasFooter = $hasFooter;
        $this->bodyClasses = $bodyClasses;
        $this->bodyStyles = $this->getStyle($bodyStyles);
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

    private function getStyle($style): ?string
    {
        if (is_array($style)) {
            return implode(' ', $style);
        }
        return $style ?? null;
    }
}
