<?php

namespace App\Services;

use App\Entities\Caches\WidgetCache;
use App\Services\ModuleService;
use Illuminate\Support\Str;

class WidgetService
{
    protected function getWidgetLists(): array
    {
        return [
            'post',
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

    public function flushWidget(string $widgetName): void
    {
        $widgetName = $this->getWidgetName($widgetName);

        if ($widgetName) {
            app(WidgetCache::class)->flushWidget($widgetName);
        }
    }

    public function flushAllWidgets(): void
    {
        foreach ($this->getWidgetLists() as $widgetName) {
            $configWidgetName = $this->getWidgetName($widgetName);

            if ($configWidgetName) {
                app(WidgetCache::class)->flushWidget($configWidgetName);
            }
        }
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