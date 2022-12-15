<?php

namespace App\Entities\Menus;

use App\Contracts\MenuInterface;
use Illuminate\Support\Str;

class UrlMenu extends BaseMenu implements MenuInterface
{
    public function __construct($menuItem, $locale)
    {
        parent::__construct($menuItem, $locale);

        $this->url = $menuItem->url;
    }

    public function getUrl(): string
    {
        $url = $this->getModel()->url ?? "";

        if (
            Str::startsWith($url, config('app.url'))
            && !$this->isUrlUntranslated($url)
        ) {
            $url = $this->getTranslatedUrl($url);
        }

        return $url ?? "";
    }

    private function isUrlUntranslated($url): bool
    {
        $routes = config('constants.untranslated_routes');

        foreach ($routes as $routeName) {
            if ($url == route($routeName)) {
                return true;
            }
        }

        return false;
    }
}