<?php

namespace App\Services;

class WidgetFrontendService extends WidgetService
{
    protected function getWidgetLists(): array
    {
        return collect(app(SettingService::class)->getFrontendWidgetLists())
            ->sortBy('order')
            ->all();
    }

    public function generateWidgets(): array
    {
        $widgets = collect([]);

        foreach ($this->getWidgetLists() as $widgetList) {
            $className = $widgetList['widget'];

            if (class_exists($className)) {
                $widgetObject = new $className($widgetList);

                if ($widgetObject->canBeAccessed()) {
                    $widgets->push($widgetObject->data());
                }
            }
        }

        return $widgets->all();
    }
}
