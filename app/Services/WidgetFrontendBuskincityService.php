<?php

namespace App\Services;

class WidgetFrontendBuskincityService extends WidgetFrontendService
{
    protected function getWidgetLists(): array
    {
        return collect([
                ...app(SettingService::class)
                    ->getArrayValueByKey('dashboard_widget_buskincity'),
                ...parent::getWidgetLists()
            ])
            ->sortBy('order')
            ->all();
    }
}
