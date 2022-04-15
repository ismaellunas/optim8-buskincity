<?php

namespace app\Services;

use App\Entities\Caches\WidgetCache;
use Illuminate\Support\Str;

class WidgetService
{
    private function getWidgetLists(): array
    {
        return [
            'post',
            'user',
            'performer_application_link',
        ];
    }

    private function getWidgetClassName($widgetName): string
    {
        return "\\App\\Entities\\Widgets\\".Str::of($widgetName)->studly()."Widget";
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

    public function getWidgetName(string $widgetName): string
    {
        return config("constants.widget_cache.{$widgetName}");
    }

    public function flushWidget(string $widgetName): void
    {
        app(WidgetCache::class)->flushWidget($this->getWidgetName($widgetName));
    }

    public function flushAllWidgets(): void
    {
        foreach ($this->getWidgetLists() as $widgetName) {
            app(WidgetCache::class)->flushWidget($this->getWidgetName($widgetName));
        }
    }
}