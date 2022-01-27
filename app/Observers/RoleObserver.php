<?php

namespace App\Observers;

use App\Services\WidgetService;

class RoleObserver
{
    public function saved()
    {
        app(WidgetService::class)->flushAllWidgets();
    }

    public function deleted()
    {
        app(WidgetService::class)->flushAllWidgets();
    }
}
