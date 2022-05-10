<?php

namespace App\Services;

class WidgetFrontendService extends WidgetService
{
    protected function getWidgetLists(): array
    {
        return [
            'qrCode',
            'upcomingEvents',
            'streetPerformersYouMightLike',
        ];
    }
}
