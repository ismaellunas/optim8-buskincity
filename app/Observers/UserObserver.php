<?php

namespace App\Observers;

use App\Services\WidgetService;

class UserObserver
{
    public function saved()
    {
        app(WidgetService::class)->flushWidget("user");
    }

    public function deleted()
    {
        app(WidgetService::class)->flushWidget("post");
        app(WidgetService::class)->flushWidget("user");
    }
}
