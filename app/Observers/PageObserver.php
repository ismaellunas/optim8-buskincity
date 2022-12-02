<?php

namespace App\Observers;

use App\Models\Page;
use App\Services\MenuService;

class PageObserver
{
    public function deleted(Page $page)
    {
        app(MenuService::class)->removeModelFromMenus($page);
    }
}