<?php

namespace App\Services;

use App\Services\ModuleService;
use Illuminate\Support\Str;

class WidgetFrontendBuskincityService extends WidgetFrontendService
{
    protected function getWidgetLists(): array
    {
        return [
            ...[
                'performer_application_link',
            ],
            ...parent::getWidgetLists()
        ];
    }
}
