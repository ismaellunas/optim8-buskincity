<?php

namespace App\Services;

use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;

class ModuleService
{
    public function getModuleListByStatus(bool $status = true): array
    {
        return Module::getByStatus($status);
    }

    public function isModuleActive(string $moduleName): bool
    {
        $moduleName = Str::lower($moduleName);
        $listModuleActive = $this->getModuleListByStatus(true);

        foreach ($listModuleActive as $module) {
            if ($module->getLowerName() === $moduleName) {
                return true;
            }
        }

        return false;
    }
}