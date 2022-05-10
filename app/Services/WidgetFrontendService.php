<?php

namespace App\Services;

class WidgetFrontendService extends WidgetService
{
    protected function getWidgetLists(): array
    {
        return [
            'qrCode',
            'socialMediaShare',
            'stripeConnect',
            'upcomingEvents',
            'streetPerformersYouMightLike',
            'wantToBecomeAStreetPerformer',
        ];
    }
}
