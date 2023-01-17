<?php

namespace App\Services;

use App\Services\ModuleService;
use Illuminate\Support\Str;

class WidgetService
{
    protected function getWidgetLists(): array
    {
        return [
            'latestRegistration',
        ];
    }

    protected function getWidgetClassName($widgetName): string
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

    public function getWidgetName(string $widgetName): ?string
    {
        return config("constants.widget_cache.{$widgetName}");
    }

    protected function moduleWidgets(): array
    {
        $modules = app(ModuleService::class)->getAllEnabledNames();

        $widgets = [];

        foreach ($modules as $module) {
            $moduleService = '\\Modules\\'.$module.'\\ModuleService';

            $methodName = 'widgets';

            if (
                class_exists($moduleService)
                && method_exists($moduleService, $methodName)
            ) {
                $widgets[$module] = $moduleService::$methodName();
            }
        }

        return $widgets;
    }

    public function generateModuleWidgets($request): array
    {
        $widgets = collect();

        foreach ($this->moduleWidgets() as $module => $moduleWidgets) {
            foreach ($moduleWidgets as $widgetName) {
                $className = "\\Modules\\{$module}\\Widgets\\".Str::of($widgetName)->studly()."Widget";

                if (class_exists($className)) {
                    $widgetObject = new $className($request);

                    if ($widgetObject->canBeAccessed()) {
                        $widgets->push($widgetObject->data());
                    }
                }
            }
        }

        return $widgets->all();
    }
}