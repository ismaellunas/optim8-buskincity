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
            'performer_application_link',
            'upcomingEvents',
            'streetPerformersYouMightLike',
            'wantToBecomeAStreetPerformer',
        ];
    }
}
