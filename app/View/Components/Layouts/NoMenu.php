<?php

namespace App\View\Components\Layouts;

use App\Models\PageTranslation;
use App\Services\SettingService;
use Illuminate\View\Component;

class NoMenu extends Component
{
    public $appCssUrl;
    public $faviconUrl;
    public $trackingCodeAfterBody;
    public $trackingCodeBeforeBody;
    public $trackingCodeInsideHead;
    public $additionalJavascript;
    public $additionalCss;
    public $fontUrls;
    public $bodyClasses;
    public $bodyStyles;

    private $settings = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        PageTranslation $page,
    ) {
        $settingService = app(SettingService::class);

        $this->settings = $page->pageTranslationSettings;

        $this->appCssUrl = $settingService->getFrontendCssUrl();
        $this->faviconUrl = $settingService->getFaviconUrl();
        $this->trackingCodeAfterBody =  $settingService->getTrackingCodeAfterBody();
        $this->trackingCodeBeforeBody = $settingService->getTrackingCodeBeforeBody();
        $this->trackingCodeInsideHead = $settingService->getTrackingCodeInsideHead();
        $this->additionalJavascript = $settingService->getAdditionalJavascript();
        $this->additionalCss = $settingService->getAdditionalCss();
        $this->fontUrls = $settingService->getFontUrls();

        $this->bodyClasses = $this->getBodyClasses();
        $this->bodyStyles = $this->getBodyStyles();
    }

    private function getBodyClasses(): array
    {
        $classes = collect();

        $classes->push('font-sans');
        $classes->push('antialiased');
        $classes->push($this->getSettingValueByKey('background_color'));

        return $classes->filter()->all();
    }

    private function getBodyStyles()
    {
        $styles = '';

        $pageHeight = $this->getSettingValueByKey('page_height');
        if ($pageHeight) {
            $styles = 'height: '.$pageHeight.'vh;';
        }

        return $styles;
    }

    private function getSettingValueByKey($key): ?string
    {
        return $this->settings->where('key', $key)->value('value') ?? null;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.layouts.no-menu');
    }
}
