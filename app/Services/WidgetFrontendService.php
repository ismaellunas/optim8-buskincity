<?php

namespace App\Services;

class WidgetFrontendService extends WidgetService
{
    protected function getWidgetLists(): array
    {
        return [
            'qrCode',
            'socialMediaShare',
            'upcomingEvents',
            'streetPerformersYouMightLike',
            'wantToBecomeAStreetPerformer',
        ];
    }
}
