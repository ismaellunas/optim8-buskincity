<?php

namespace App\Services;

use App\Services\ModuleService;
use Illuminate\Support\Collection;
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

    public function storedWidgets(): Collection
    {
        $widgets = collect();

        $widgetSettings = app(SettingService::class)
            ->adminDashboardWidgets()
            ->sortBy('order');

        foreach ($widgetSettings as $widgetSetting) {
            $className = $widgetSetting['widget'];

            if (class_exists($className)) {
                $widgetObject = new $className($widgetSetting);

                if ($widgetObject->canBeAccessed()) {
                    $widgets->push($widgetObject->data());
                }
            }
        }

        return $widgets;
    }

    public function getWidgetHandler(string $uuid): object
    {
        $widgetSetting = app(SettingService::class)
            ->adminDashboardWidgets()
            ->first(fn ($widget) => $widget['uuid'] == $uuid);

        $className = $widgetSetting['widget'];

        if ($className && class_exists($className)) {
            return new $className($widgetSetting);
        }

        return null;
    }
}