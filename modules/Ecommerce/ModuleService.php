<?php

namespace Modules\Ecommerce;

class ModuleService
{
    public static function permissions(): array
    {
        return config('ecommerce.permissions');
    }
}
