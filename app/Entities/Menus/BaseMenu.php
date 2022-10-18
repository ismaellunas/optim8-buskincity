<?php

namespace App\Entities\Menus;

use App\Models\MenuItem;
use App\Services\TranslationService;
use App\Services\LoginService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

abstract class BaseMenu
{
    public $id;
    public $title;
    public $type;
    public $url;
    public $order;
    public $icon;
    public $is_blank;
    public $parent_id;
    public $menu_id;
    public $page_id;
    public $post_id;
    public $category_id;
    public $children;

    protected $locale;
    protected $currentLocale;
    protected $menuItem = null;
    protected $modelName = MenuItem::class;

    public function __construct($menuItem, $locale)
    {
        $this->locale = $locale;
        $this->currentLocale = TranslationService::currentLanguage();
        $this->menuItem = $menuItem;

        foreach ($menuItem->getAttributes() as $attribute => $value) {
            $this->$attribute = $value;
        }
    }

    protected function getEagerLoads(): array
    {
        return [];
    }

    public function getModel(): ?MenuItem
    {
        if ($this->menuItem == null && $this->id) {
            $this->loadModel();
        }

        return $this->menuItem;
    }

    protected function loadModel()
    {
        $this->menuItem = $this->modelName::
            when($this->getEagerLoads(), function ($query) {
                $query->with($this->getEagerLoads());
            })
            ->find($this->id);
    }

    abstract public function getUrl();

    protected function getTranslatedUrl(string $url): string
    {
        return LaravelLocalization::localizeURL($url, $this->getLocaleTranslation());
    }

    public function getTarget(): ?string
    {
        return $this->getModel()->is_blank ? "_blank" : null;
    }

    public function isInternalLink(): bool
    {
        return Str::startsWith($this->getUrl(), config('app.url'));
    }

    public function isActive(string $url): bool
    {
        return $this->getUrl() == $url;
    }

    protected function getLocaleTranslation()
    {
        if (
            Auth::check()
            && LoginService::isUserHomeUrl()
        ) {
            return Auth::user()->originLanguageCode ?? $this->locale;
        }

        return $this->currentLocale;
    }
}
