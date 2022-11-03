<?php

namespace App\View\Components\Headers;

use App\Services\LoginService;
use App\Services\StorageService;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

abstract class BaseNavbarLayout extends Component
{
    public $menus;
    public $currentLanguage;
    public $logoUrl;
    public $languageOptions;
    public $layoutName;

    public function __construct($menus, $currentLanguage, $logoUrl, $languageOptions)
    {
        $this->currentLanguage = $currentLanguage;
        $this->menus = $menus ?? [];
        $this->logoUrl = $logoUrl;
        $this->languageOptions = $languageOptions;
    }

    public function logo(): string
    {
        return $this->logoUrl
            ?? StorageService::getImageUrl(
                config('constants.default_images.logo')
            );
    }

    public function dashboardUrl(): string
    {
        if (LoginService::isAdminHomeUrl()) {
            return route('admin.dashboard');
        }
        return route('dashboard');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.headers.'.$this->layoutName);
    }
}