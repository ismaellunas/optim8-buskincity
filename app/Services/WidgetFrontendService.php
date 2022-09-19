<?php

namespace App\Services;

use App\Services\ModuleService;
use Illuminate\Support\Str;

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
