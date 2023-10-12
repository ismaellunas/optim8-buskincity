<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module as ModuleFacade;

abstract class BaseModuleService
{
    public static function getName(): string
    {
        $moduleName = strtolower(explode('\\', get_class(new static))[1]);

        return ModuleFacade::find(config($moduleName.'.name'))->get('name');
    }

    public function getTerm(string $key): string
    {
        return Str::snake($this->getName()).'_term.'.Str::snake($key);
    }

    public function model(): Module
    {
        return app(ModuleService::class)
            ->modules()
            ->firstWhere('name', '=', $this->getName());
    }

    public function isModuleActive(): bool
    {
        return app(ModuleService::class)->isModuleActive($this->getName());
    }
}
