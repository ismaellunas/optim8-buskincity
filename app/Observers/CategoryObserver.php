<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\MenuService;

class CategoryObserver
{
    public function deleted(Category $category)
    {
        app(MenuService::class)->removeModelFromMenus($category);
    }
}
