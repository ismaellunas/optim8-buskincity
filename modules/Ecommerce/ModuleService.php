<?php

namespace Modules\Ecommerce;

class ModuleService
{
    const MEDIA_TYPE_PRODUCT = 16;

    public static function permissions(): array
    {
        return config('ecommerce.permissions');
    }

    public static function productMediaFolder(): string
    {
        return 'product';
    }
}
