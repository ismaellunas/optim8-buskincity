<?php

namespace app\Services;

class WidgetService
{
    private function getWidgetLists(): array
    {
        return [
            'post',
            'user',
        ];
    }

    private function getWidgetClassName($widgetName): string
    {
        return "\\App\\Entities\\Widgets\\".ucfirst($widgetName)."Widget";
    }

    public function generateWidgets(): array
    {
        $widgets = collect([]);

        foreach ($this->getWidgetLists() as $widget) {
            $className = $this->getWidgetClassName($widget);

            if (class_exists($className)) {
                $widgetObject = new $className();

                if ($widgetObject->canBeAccessed()) {
                    $widgets->push($widgetObject->data());
                }
            }
        }

        return $widgets->all();
    }
}