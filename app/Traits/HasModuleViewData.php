<?php

namespace App\Traits;

use App\Services\ModuleService;
use Illuminate\Support\Str;

trait HasModuleViewData
{
    protected function getModulesViewData($method = 'Admin'): array
    {
        $modules = app(ModuleService::class)->getAllEnabledNames();

        $data = [];

        foreach ($modules as $module) {
            $moduleService = '\\Modules\\'.$module.'\\ModuleService';

            $methodName = 'get'.$method.'Data';

            if (
                class_exists($moduleService)
                && method_exists($moduleService, $methodName)
            ) {
                $data[Str::snake($module)] = $moduleService::$methodName();
            }
        }

        return [
            'modules' => $data
        ];
    }
}
