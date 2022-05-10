<?php

namespace App\Services;

class WidgetFrontendService extends WidgetService
{
    protected function getWidgetLists(): array
    {
        return [
            'qrCode',
            'performer_application_link',
            'upcomingEvents',
            'streetPerformersYouMightLike',
            'wantToBecomeAStreetPerformer',
        ];
    }
}
