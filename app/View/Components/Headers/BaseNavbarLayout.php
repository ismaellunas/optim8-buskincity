<?php

namespace App\View\Components\Headers;

use App\Services\LoginService;
use App\Services\StorageService;
use Illuminate\Support\Str;
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

    public function dashboardUrl(): string
    {
        if (LoginService::isAdminHomeUrl()) {
            return route('admin.dashboard');
        }
        return route('dashboard');
    }

    public function isActive(string $url = null): bool
    {
        return request()->url() == $this->transformUrl($url);
    }

    private function transformUrl(string $url): string
    {
        if (
            Str::startsWith($url, config('app.url'))
            && Str::endsWith($url, '/')
        ) {
            return Str::substr($url, 0, -1);
        }

        return $url;
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