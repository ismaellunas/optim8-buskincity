<?php

namespace Modules\Ecommerce;

use Illuminate\Support\Collection;

class ModuleService
{
    const MEDIA_TYPE_PRODUCT = 16;

    public static function getName()
    {
        return config('ecommerce.name');
    }

    public static function permissions(): Collection
    {
        return collect(config('ecommerce.permissions'));
    }

    public static function productMediaFolder(): string
    {
        return 'product';
    }

    public static function maxProductMediaNumber(): int
    {
        return 10;
    }

    public static function maxProductFileSize(): int
    {
        return 5 * config('constants.one_megabyte');
    }

    public static function tablePrefix()
    {
        return config('getcandy.database.table_prefix');
    }
}
