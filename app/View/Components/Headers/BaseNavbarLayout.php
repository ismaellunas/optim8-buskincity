<?php

namespace App\View\Components\Headers;

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
        return $this->logoUrl ?? 'https://dummyimage.com/48x28/e5e5e5/000000.png&text=B+752';
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